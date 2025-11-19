<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: main.html"); exit; } // main.php 대신 main.html로 수정

$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

$user_id = $_SESSION['user_id'];
// POST 또는 GET으로 game_id를 받음 (충전 후 재진입 시 GET 사용)
$game_id = isset($_POST['game_id']) ? intval($_POST['game_id']) : (isset($_GET['game_id']) ? intval($_GET['game_id']) : 0);
// -------------------- 컨텍스트 보존을 위한 Referrer 값 획득 --------------------
$referrer = isset($_POST['referrer']) ? $_POST['referrer'] : (isset($_GET['referrer']) ? $_GET['referrer'] : 'game_search.php');


// -----------------------------------------------------------
// 1. 게임 및 세일 정보 조회 (가장 높은 할인율 1개만)
// -----------------------------------------------------------
$sql = "
SELECT G.*, 
       COALESCE(S.sale_id, 1) AS sale_id, 
       COALESCE(S.discount_rate, 0.0) AS discount_rate, 
       COALESCE(S.discount_period, '') AS discount_period, 
       COALESCE(S.reason, '') AS reason
FROM Game G
LEFT JOIN (
    SELECT * FROM Sale 
    WHERE game_id = ?
      AND NOW() BETWEEN STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', 1), '%Y-%m-%d') 
                     AND STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', -1), '%Y-%m-%d')
    ORDER BY discount_rate DESC
    LIMIT 1
) S ON G.game_id = S.game_id
WHERE G.game_id = ?
";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "ii", $game_id, $game_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$row = mysqli_fetch_assoc($result)) {
    echo "<script>alert('게임 정보를 찾을 수 없습니다.');location.href='user_main.php';</script>";
    exit;
}
mysqli_stmt_close($stmt);


$game_name = $row['name'];
$price = $row['price'];
$sale_id = $row['sale_id'];
$discount_rate = $row['discount_rate'];
$discounted_price = $price * (1 - $discount_rate);


// -----------------------------------------------------------
// 2. 현재 유저의 bank(스팀월렛) 금액 조회 (Prepared Statement 적용)
// -----------------------------------------------------------
$sql_bank = "SELECT bank FROM Customer WHERE customer_id = ?";
$stmt_bank = mysqli_prepare($db, $sql_bank);
mysqli_stmt_bind_param($stmt_bank, "i", $user_id);
mysqli_stmt_execute($stmt_bank);
$bank_result = mysqli_stmt_get_result($stmt_bank);
$bank_row = mysqli_fetch_assoc($bank_result);
$current_bank = $bank_row['bank'];
mysqli_stmt_close($stmt_bank);

// -----------------------------------------------------------
// 3. 충전 처리 (POST charge_amount)
// -----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charge_amount'])) {
    $charge = floatval($_POST['charge_amount']);
    if ($charge > 0) {
        $sql_charge = "UPDATE Customer SET bank = bank + ? WHERE customer_id = ?";
        $stmt_charge = mysqli_prepare($db, $sql_charge);
        mysqli_stmt_bind_param($stmt_charge, "di", $charge, $user_id);
        mysqli_stmt_execute($stmt_charge);
        mysqli_stmt_close($stmt_charge);
        // 충전 후 현재 결제 페이지로 재진입 (referrer와 game_id 유지)
        echo "<script>alert('{$charge}원이 충전되었습니다.');location.href='purchase.php?game_id={$game_id}&referrer={$referrer}';</script>";
        mysqli_close($db);
        exit;
    }
}

// -----------------------------------------------------------
// 4. 결제 완료 POST 처리 (Transaction 및 PlayList 등록 적용)
// -----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method']) && !isset($_POST['charge_amount'])) {
    $payment_method = $_POST['payment_method'];
    $redirect_url = $referrer; // 동적 리다이렉트 URL 사용

    // 스팀월렛 결제 잔액 확인
    if ($payment_method == 'Steam Wallet' && $current_bank < $discounted_price) {
        echo "<script>alert('스팀월렛 잔액이 부족합니다.');location.href='{$redirect_url}';</script>";
        exit;
    }

    // 트랜잭션 시작: 데이터 무결성 보장
    mysqli_begin_transaction($db);

    try {
        // A. order_id 자동 생성 (MAX + 1 방식은 동시성 문제 있으나, 기존 코드 유지 및 트랜잭션 보호)
        $order_row = mysqli_fetch_assoc(mysqli_query($db, "SELECT IFNULL(MAX(order_id), 0) + 1 AS next_order_id FROM OrderList"));
        $order_id = $order_row['next_order_id'];
        
        // B. 주문 추가 (OrderList INSERT)
        $sql_order = "INSERT INTO OrderList (order_id, customer_id, game_id, sale_id, payed_price, order_time, payment_method, discount_rate)
                       VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";
        $stmt_order = mysqli_prepare($db, $sql_order);
        mysqli_stmt_bind_param($stmt_order, "iiiidss", $order_id, $user_id, $game_id, $sale_id, $discounted_price, $payment_method, $discount_rate);
        if (!mysqli_stmt_execute($stmt_order)) { throw new Exception("OrderList INSERT Fail"); }
        mysqli_stmt_close($stmt_order);

        // C. 라이브러리 추가 (PlayList INSERT) -> CRITICAL BUG FIX
        $sql_playlist = "INSERT IGNORE INTO PlayList (customer_id, game_id, play_time) VALUES (?, ?, 0)";
        $stmt_playlist = mysqli_prepare($db, $sql_playlist);
        mysqli_stmt_bind_param($stmt_playlist, "ii", $user_id, $game_id);
        if (!mysqli_stmt_execute($stmt_playlist)) { throw new Exception("PlayList INSERT Fail"); }
        mysqli_stmt_close($stmt_playlist);

        // D. 스팀월렛 결제라면 잔액 차감 (Customer UPDATE)
        if ($payment_method == 'Steam Wallet') {
            $sql_deduct = "UPDATE Customer SET bank = bank - ? WHERE customer_id = ?";
            $stmt_deduct = mysqli_prepare($db, $sql_deduct);
            mysqli_stmt_bind_param($stmt_deduct, "di", $discounted_price, $user_id);
            if (!mysqli_stmt_execute($stmt_deduct)) { throw new Exception("Bank Deduction Fail"); }
            mysqli_stmt_close($stmt_deduct);
        }

        // 모든 쿼리 성공 시 커밋
        mysqli_commit($db);
        echo "<script>alert('구매가 완료되었습니다. 라이브러리에 등록되었습니다.');location.href='{$redirect_url}';</script>";

    } catch (Exception $e) {
        // 오류 발생 시 롤백 (잔액 복구, OrderList/PlayList 삽입 취소)
        mysqli_rollback($db);
        echo "<script>alert('구매 중 오류가 발생했습니다: ". $e->getMessage() ."');location.href='{$redirect_url}';</script>";
    }
    
    mysqli_close($db);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>게임 결제</title>
    </head>
<body>
<div class="container">
    <div class="section-title">게임 정보</div>
    <table class="info-table">
        <tr><th>게임명</th><td><?=htmlspecialchars($game_name)?></td></tr>
        <tr><th>정가</th><td><?=number_format($price)?> 원</td></tr>
        <?php if ($discount_rate > 0): ?>
            <tr><th>할인율</th>
<td><?= rtrim(rtrim(number_format($discount_rate * 100, 2, '.', ''), '0'), '.') . '%' ?></td></tr>
        <?php endif; ?>
    </table>

    <div class="section-title">결제 수단 선택</div>
    <div class="wallet-box">
        <span class="wallet-label">스팀 지갑 잔액 : </span>
        <span style="color:blue; font-weight:bold;"><?=number_format($current_bank)?> 원</span>
        <form method="post" class="charge-form" style="display:inline;">
            <input type="hidden" name="game_id" value="<?=$game_id?>">
            <input type="hidden" name="referrer" value="<?=$referrer?>"> <input type="number" name="charge_amount" min="1" placeholder="충전 금액" style="width:100px;">
            <input type="submit" value="충전">
        </form>
    </div>

    <form method="post">
        <input type="hidden" name="game_id" value="<?=$game_id?>">
        <input type="hidden" name="payment_method" id="payment_method" value="">
        <input type="hidden" name="referrer" value="<?=$referrer?>"> <div class="pay-btns">
            </div>
        <div class="pay-summary">
            <del><?=number_format($price)?>원</del>
            <span style="font-weight:bold"><?=number_format($discounted_price)?>원</span>
        </div>
        <button type="submit" id="pay-submit" class="pay-submit" disabled>결제 완료</button>
    </form>
    <br>
    <a href="<?=$referrer?>">← 게임 목록</a>
</div>
</body>
</html>
<?php mysqli_close($db); ?>
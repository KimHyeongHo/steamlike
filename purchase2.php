<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: main.php"); exit; }

$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

$user_id = $_SESSION['user_id'];
$game_id = isset($_POST['game_id']) ? intval($_POST['game_id']) : 0;

// 게임 및 세일 정보 조회 (가장 높은 할인율 1개만)
$sql = "
SELECT G.*, 
       COALESCE(S.sale_id, 1) AS sale_id, 
       COALESCE(S.discount_rate, 0.0) AS discount_rate, 
       COALESCE(S.discount_period, '') AS discount_period, 
       COALESCE(S.reason, '') AS reason
FROM Game G
LEFT JOIN (
    SELECT * FROM Sale 
    WHERE game_id = $game_id
      AND NOW() BETWEEN STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', 1), '%Y-%m-%d') 
                     AND STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', -1), '%Y-%m-%d')
    ORDER BY discount_rate DESC
    LIMIT 1
) S ON G.game_id = S.game_id
WHERE G.game_id = $game_id
";
$result = mysqli_query($db, $sql);
if (!$row = mysqli_fetch_assoc($result)) {
    echo "게임 정보를 찾을 수 없습니다.";
    exit;
}

$game_name = $row['name'];
$genre = $row['genre'];
$developer = $row['developer'];
$price = $row['price'];
$rating = $row['rating'];
$sale_id = $row['sale_id'];
$discount_rate = $row['discount_rate'];
$discount_period = $row['discount_period'];
$reason = $row['reason'];
$discounted_price = $price * (1 - $discount_rate);

// 현재 유저의 bank(스팀월렛) 금액 조회
$bank_row = mysqli_fetch_assoc(mysqli_query($db, "SELECT bank FROM Customer WHERE customer_id = $user_id"));
$current_bank = $bank_row['bank'];

// 충전 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charge_amount'])) {
    $charge = floatval($_POST['charge_amount']);
    if ($charge > 0) {
        mysqli_query($db, "UPDATE Customer SET bank = bank + $charge WHERE customer_id = $user_id");
        echo "<script>alert('{$charge}원이 충전되었습니다.');location.href='sale_search.php';</script>";
        mysqli_close($db);
        exit;
    }
}

// 결제 완료 POST 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method']) && !isset($_POST['charge_amount'])) {
    $payment_method = $_POST['payment_method'];

    // 스팀월렛 결제라면 잔액 확인
    if ($payment_method == 'Steam Wallet' && $current_bank < $discounted_price) {
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'sale_search.php';
    echo "<script>alert('스팀월렛 잔액이 부족합니다.');location.href='sale_search.php';</script>";
    exit;
}


    // order_id 자동 생성
    $order_row = mysqli_fetch_assoc(mysqli_query($db, "SELECT IFNULL(MAX(order_id), 0) + 1 AS next_order_id FROM OrderList"));
    $order_id = $order_row['next_order_id'];

    // 주문 추가
    $sql_insert = "INSERT INTO OrderList (order_id, customer_id, game_id, sale_id, payed_price, order_time, payment_method, discount_rate)
                   VALUES ($order_id, $user_id, $game_id, $sale_id, $discounted_price, NOW(), '$payment_method', $discount_rate)";
    if (mysqli_query($db, $sql_insert)) {
        // 스팀월렛 결제라면 잔액 차감
        if ($payment_method == 'Steam Wallet') {
            mysqli_query($db, "UPDATE Customer SET bank = bank - $discounted_price WHERE customer_id = $user_id");
        }
        echo "<script>alert('구매가 완료되었습니다.');location.href='sale_search.php';</script>";
        mysqli_close($db);
        exit;
    } else {
        echo "<script>alert('구매 중 오류가 발생했습니다.');location.href='sale_search.php';</script>";
        mysqli_close($db);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>게임 결제</title>
    <style>
        body { background: #f8f8f8; font-family: sans-serif; }
        .container { width: 420px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.12); padding: 30px 40px 30px 40px; }
        .section-title { font-size: 1.2em; color: #1976d2; margin-bottom: 12px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .info-table th, .info-table td { border: 1px solid #ccc; padding: 8px 10px; }
        .pay-btns { display: flex; gap: 8px; margin-bottom: 18px; }
        .pay-btn { flex: 1; padding: 10px 0; border: 1.5px solid #1976d2; border-radius: 5px; background: #fff; color: #1976d2; cursor: pointer; font-weight: bold; transition: background 0.2s, color 0.2s; }
        .pay-btn.selected { background: #1976d2; color: #fff; }
        .pay-summary { margin-bottom: 18px; font-size: 1.1em; }
        .pay-summary del { color: #888; margin-right: 10px; }
        .pay-submit { width: 100%; padding: 12px 0; background: #43a047; color: #fff; border: none; border-radius: 5px; font-size: 1.1em; font-weight: bold; cursor: pointer; opacity: 0.6; transition: opacity 0.2s; }
        .pay-submit.enabled { opacity: 1; cursor: pointer; }
        .wallet-box { margin-bottom: 18px; padding: 10px 15px; background: #f0f7ff; border-radius: 7px; border: 1px solid #b5d0ee; }
        .wallet-label { color: #1976d2; font-weight: bold; }
        .charge-form { display: inline; margin-left: 20px; }
    </style>
    <script>
        function selectMethod(method) {
            document.getElementById('payment_method').value = method;
            var btns = document.querySelectorAll('.pay-btn');
            btns.forEach(function(btn) {
                btn.classList.remove('selected');
                if (btn.dataset.method === method) btn.classList.add('selected');
            });
            document.getElementById('pay-submit').disabled = false;
            document.getElementById('pay-submit').classList.add('enabled');
        }
    </script>
</head>
<body>
<div class="container">
    <!-- 게임 파트 -->
    <div class="section-title">게임 정보</div>
    <table class="info-table">
        <tr><th>게임명</th><td><?=htmlspecialchars($game_name)?></td></tr>
        <tr><th>장르</th><td><?=htmlspecialchars($genre)?></td></tr>
        <tr><th>개발사</th><td><?=htmlspecialchars($developer)?></td></tr>
        <tr><th>평점</th><td><?=htmlspecialchars($rating)?></td></tr>
        <tr><th>정가</th><td><?=number_format($price)?> 원</td></tr>
        <?php if ($discount_rate > 0): ?>
            <tr><th>할인율</th>
<td><?= rtrim(rtrim(number_format($discount_rate * 100, 2, '.', ''), '0'), '.') . '%' ?></td>
            <tr><th>할인 기간</th><td><?=htmlspecialchars($discount_period)?></td></tr>
            <tr><th>할인 사유</th><td><?=htmlspecialchars($reason)?></td></tr>
        <?php endif; ?>
    </table>

    <!-- 스팀월렛(잔액) 및 충전 -->
     <div class="section-title">결제 수단 선택</div>
    <div class="wallet-box">
        <span class="wallet-label">스팀 지갑 잔액 : </span>
        <span style="color:blue; font-weight:bold;"><?=number_format($current_bank)?> 원</span>
        <form method="post" class="charge-form" style="display:inline;">
            <input type="hidden" name="game_id" value="<?=$game_id?>">
            <input type="number" name="charge_amount" min="1" placeholder="충전 금액" style="width:100px;">
            <input type="submit" value="충전">
        </form>
    </div>

    <!-- 결제 파트 -->
    <form method="post">
        <input type="hidden" name="game_id" value="<?=$game_id?>">
        <input type="hidden" name="payment_method" id="payment_method" value="">
        <div class="pay-btns">
            <button type="button" class="pay-btn" data-method="Steam Wallet" onclick="selectMethod('Steam Wallet')">Steam Wallet</button>
            <button type="button" class="pay-btn" data-method="KakaoPay" onclick="selectMethod('KakaoPay')">KakaoPay</button>
            <button type="button" class="pay-btn" data-method="Credit Card" onclick="selectMethod('Credit Card')">Credit Card</button>
            <button type="button" class="pay-btn" data-method="Paypal" onclick="selectMethod('Paypal')">Paypal</button>
            <button type="button" class="pay-btn" data-method="NaverPay" onclick="selectMethod('NaverPay')">NaverPay</button>
            <button type="button" class="pay-btn" data-method="Toss" onclick="selectMethod('Toss')">Toss</button>
        </div>
        <div class="pay-summary">
            <del><?=number_format($price)?>원</del>
            <span style="font-weight:bold"><?=number_format($discounted_price)?>원</span>
        </div>
        <button type="submit" id="pay-submit" class="pay-submit" disabled>결제 완료</button>
    </form>
    <br>
    <a href="sale_search.php">← 게임 목록</a>
</div>
</body>
</html>
<?php mysqli_close($db); ?>

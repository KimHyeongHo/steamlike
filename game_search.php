<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: main.php"); exit; }

$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

$user_id = $_SESSION['user_id'];
// game_id가 9999가 아닌 게임만 조회
$sql = "SELECT * FROM Game WHERE game_id <> 9999";
$ret = mysqli_query($db, $sql);

echo "<H1>게임 검색 결과</H1>";
echo "<TABLE border=1>";
echo "<TR><TH>이름</TH><TH>장르</TH><TH>가격</TH><TH>할인된 가격</TH><TH>구매</TH></TR>";

while($row = mysqli_fetch_array($ret)) {
    $game_id = $row['game_id'];
    $price = $row['price'];

    // 할인율 계산 (Sale 테이블에서 현재 적용 중인 가장 높은 할인율)
    $sql_sale = "SELECT discount_rate FROM Sale WHERE game_id = $game_id AND NOW() BETWEEN STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', 1), '%Y-%m-%d') AND STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', -1), '%Y-%m-%d') ORDER BY discount_rate DESC LIMIT 1";
    $result_sale = mysqli_query($db, $sql_sale);
    if ($row_sale = mysqli_fetch_assoc($result_sale)) {
        $discount_rate = $row_sale['discount_rate'];
    } else {
        $discount_rate = 0.0;
    }
    $discounted_price = $price * (1 - $discount_rate);

    // 구매 여부 확인 (OrderList에 존재하는지)
    $sql_owned = "SELECT 1 FROM OrderList WHERE customer_id = $user_id AND game_id = $game_id LIMIT 1";
    $result_owned = mysqli_query($db, $sql_owned);
    $owned = mysqli_fetch_assoc($result_owned) ? true : false;

    echo "<TR>";
    echo "<TD>" . htmlspecialchars($row['name']) . "</TD>";
    echo "<TD>" . htmlspecialchars($row['genre']) . "</TD>";
    echo "<TD>" . number_format($price) . "</TD>";
    echo "<TD>" . number_format($discounted_price) . "</TD>";
    echo "<TD>";
    if ($owned) {
        echo "<span style='color:gray;font-weight:bold'>보유중</span>";
    } else {
        // 구매 버튼
        echo "<form method='post' action='purchase.php' style='margin:0'>";
        echo "<input type='hidden' name='game_id' value='$game_id'>";
        echo "<button type='submit'>구매</button>";
        echo "</form>";
    }
    echo "</TD>";
    echo "</TR>";
}

echo "</TABLE>";
echo "<BR><A HREF='user_main.php'>← 사용자 메인</A>";
mysqli_close($db);
?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: main.php"); exit; }

$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

$user_id = $_SESSION['user_id'];

// 현재 진행중인 세일 중, 같은 게임에 대해 가장 높은 할인율 1개만
$sql = "
SELECT S.sale_id, G.game_id, G.name AS game_name, G.price, S.discount_rate, S.discount_period, S.reason
FROM (
    SELECT *, ROW_NUMBER() OVER (PARTITION BY game_id ORDER BY discount_rate DESC) AS rn
    FROM Sale
    WHERE sale_id > 1
      AND NOW() BETWEEN
        STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', 1), '%Y-%m-%d')
        AND
        STR_TO_DATE(SUBSTRING_INDEX(discount_period, ' ~ ', -1), '%Y-%m-%d')
) S
JOIN Game G ON S.game_id = G.game_id
WHERE S.rn = 1
ORDER BY S.sale_id
";
$ret = mysqli_query($db, $sql);

echo "<H1>세일 목록</H1>";
echo "<TABLE border=1>";
echo "<TR>
<TH>게임 이름</TH>
<TH>현재 가격</TH>
<TH>할인율</TH>
<TH>할인된 가격</TH>
<TH>할인 기간</TH>
<TH>사유</TH>
<TH>구매</TH>
</TR>";

while($row = mysqli_fetch_array($ret)) {
    $game_id = $row['game_id'];
    $price = $row['price'];
    $discount_rate = $row['discount_rate'];
    $discounted_price = $price * (1 - $discount_rate);

    // 구매 여부 확인
    $sql_owned = "SELECT 1 FROM OrderList WHERE customer_id = $user_id AND game_id = $game_id LIMIT 1";
    $result_owned = mysqli_query($db, $sql_owned);
    $owned = mysqli_fetch_assoc($result_owned) ? true : false;

    echo "<TR>";
    echo "<TD>", htmlspecialchars($row['game_name']), "</TD>";
    echo "<TD>", number_format($price), "</TD>";
    echo "<TD>", ($discount_rate * 100), "%</TD>";
    echo "<TD>", number_format($discounted_price), "</TD>";
    echo "<TD>", htmlspecialchars($row['discount_period']), "</TD>";
    echo "<TD>", htmlspecialchars($row['reason']), "</TD>";
    echo "<TD>";
    if ($owned) {
        echo "<span style='color:gray;font-weight:bold'>보유중</span>";
    } else {
        echo "<form method='post' action='purchase2.php' style='margin:0'>";
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

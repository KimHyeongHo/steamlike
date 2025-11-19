<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: main.php"); exit; }

$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

$user_id = $_SESSION['user_id'];
// 게임 이름, 결제수단, 주문시간, 할인율, 결제금액을 출력
$sql = "
SELECT G.name AS game_name, O.payment_method, O.order_time, O.discount_rate, O.payed_price
FROM OrderList O
JOIN Game G ON O.game_id = G.game_id
WHERE O.customer_id = '$user_id'
ORDER BY O.order_time DESC
";
$ret = mysqli_query($db, $sql);

echo "<H1>내 주문 목록</H1>";
echo "<TABLE border=1>";
echo "<TR>
<TH>게임 이름</TH>
<TH>결제수단</TH>
<TH>주문시간</TH>
<TH>할인율</TH>
<TH>결제금액</TH>
</TR>";

while($row = mysqli_fetch_array($ret)) {
    echo "<TR>";
    echo "<TD>", htmlspecialchars($row['game_name']), "</TD>";
    echo "<TD>", htmlspecialchars($row['payment_method']), "</TD>";
    echo "<TD>", htmlspecialchars($row['order_time']), "</TD>";
    echo "<TD>", $row['discount_rate'], "</TD>";
    echo "<TD>", $row['payed_price'], "</TD>";
    echo "</TR>";
}
echo "</TABLE>";
echo "<BR><A HREF='user_main.php'>← 사용자 메인</A>";
mysqli_close($db);
?>

<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$sql = "SELECT * FROM OrderList";
$ret = mysqli_query($db, $sql);

echo "<H1>오더리스트 관리</H1>";
echo "<A HREF='orderlist_insert.php'>[오더리스트 추가]</A><BR><BR>";
echo "<TABLE border=1>";
echo "<TR>
<TH>주문ID</TH>
<TH>고객ID</TH>
<TH>게임ID</TH>
<TH>세일ID</TH>
<TH>결제금액</TH>
<TH>주문시간</TH>
<TH>결제수단</TH>
<TH>할인율</TH>
<TH>수정</TH>
<TH>삭제</TH>
</TR>";

while($row = mysqli_fetch_array($ret)) {
    echo "<TR>";
    echo "<TD>", $row['order_id'], "</TD>";
    echo "<TD>", $row['customer_id'], "</TD>";
    echo "<TD>", $row['game_id'], "</TD>";
    echo "<TD>", $row['sale_id'], "</TD>";
    echo "<TD>", $row['payed_price'], "</TD>";
    echo "<TD>", $row['order_time'], "</TD>";
    echo "<TD>", $row['payment_method'], "</TD>";
    echo "<TD>", $row['discount_rate'], "</TD>";
    echo "<TD><A HREF='orderlist_update.php?order_id=".$row['order_id']."'>수정</A></TD>";
    echo "<TD><A HREF='orderlist_delete.php?order_id=".$row['order_id']."'>삭제</A></TD>";
    echo "</TR>";
}
echo "</TABLE>";
echo "<BR><A HREF='admin_main.php'>← 관리자 메인</A>";
mysqli_close($db);
?>

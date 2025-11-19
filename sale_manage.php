<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$sql = "SELECT * FROM Sale";
$ret = mysqli_query($db, $sql);

echo "<H1>세일 관리</H1>";
echo "<A HREF='sale_insert.php'>[세일 추가]</A><BR><BR>";
echo "<TABLE border=1>";
echo "<TR>
<TH>세일ID</TH>
<TH>게임ID</TH>
<TH>할인율</TH>
<TH>할인 기간</TH>
<TH>사유</TH>
<TH>수정</TH>
<TH>삭제</TH>
</TR>";

while($row = mysqli_fetch_array($ret)) {
    echo "<TR>";
    echo "<TD>", $row['sale_id'], "</TD>";
    echo "<TD>", $row['game_id'], "</TD>";
    echo "<TD>", $row['discount_rate'], "</TD>";
    echo "<TD>", $row['discount_period'], "</TD>";
    echo "<TD>", $row['reason'], "</TD>";
    echo "<TD><A HREF='sale_update.php?sale_id=".$row['sale_id']."'>수정</A></TD>";
    echo "<TD><A HREF='sale_delete.php?sale_id=".$row['sale_id']."'>삭제</A></TD>";
    echo "</TR>";
}
//세일 관리에서 할인율을 수정해도 오더리스트에는 반영되지 않도록 설계
echo "</TABLE>";
echo "<BR><A HREF='admin_main.php'>← 관리자 메인</A>";
mysqli_close($db);
?>

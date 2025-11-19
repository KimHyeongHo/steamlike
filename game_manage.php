<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$sql = "SELECT * FROM Game";
$ret = mysqli_query($db, $sql);
echo "<H1>게임 관리</H1>";
echo "<A HREF='game_insert.php'>[게임 추가]</A><BR><BR>";
echo "<TABLE border=1>";
echo "<TR><TH>게임ID</TH><TH>이름</TH><TH>장르</TH><TH>가격</TH><TH>수정</TH><TH>삭제</TH></TR>";
while($row = mysqli_fetch_array($ret)) {
    echo "<TR>";
    echo "<TD>", $row['game_id'], "</TD>";
    echo "<TD>", $row['name'], "</TD>";
    echo "<TD>", $row['genre'], "</TD>";
    echo "<TD>", $row['price'], "</TD>";
    echo "<TD><A HREF='game_update.php?game_id=".$row['game_id']."'>수정</A></TD>";
    echo "<TD><A HREF='game_delete.php?game_id=".$row['game_id']."'>삭제</A></TD>";
    echo "</TR>";
}
echo "</TABLE>";
echo "<BR><A HREF='admin_main.php'>← 관리자 메인</A>";
mysqli_close($db);
?>

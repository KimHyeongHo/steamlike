<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

// PlayList와 Game을 조인해서 게임 이름(name) 출력
$sql = "SELECT P.customer_id, P.game_id, G.name, P.play_time 
        FROM PlayList P 
        JOIN Game G ON P.game_id = G.game_id";
$ret = mysqli_query($db, $sql);

echo "<H1>플레이리스트 관리</H1>";
echo "<A HREF='playlist_insert.php'>[플레이리스트 추가]</A><BR><BR>";
echo "<TABLE border=1>";
echo "<TR>
<TH>고객ID</TH>
<TH>게임ID</TH>
<TH>게임 이름</TH>
<TH>플레이타임</TH>
<TH>수정</TH>
<TH>삭제</TH>
</TR>";

while($row = mysqli_fetch_array($ret)) {
    echo "<TR>";
    echo "<TD>", $row['customer_id'], "</TD>";
    echo "<TD>", $row['game_id'], "</TD>";
    echo "<TD>", htmlspecialchars($row['name']), "</TD>";
    echo "<TD>", $row['play_time'], "</TD>";
    echo "<TD><A HREF='playlist_update.php?customer_id=", $row['customer_id'], "&game_id=", $row['game_id'], "'>수정</A></TD>";
    echo "<TD><A HREF='playlist_delete.php?customer_id=", $row['customer_id'], "&game_id=", $row['game_id'], "'>삭제</A></TD>";
    echo "</TR>";
}
echo "</TABLE>";
echo "<BR><A HREF='admin_main.php'>← 관리자 메인</A>";
mysqli_close($db);
?>

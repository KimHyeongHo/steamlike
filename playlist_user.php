<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: main.php"); exit; }

$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

$user_id = $_SESSION['user_id'];

// 게임 ID 대신 게임 이름(name)과 플레이타임을 출력
$sql = "
SELECT G.name AS game_name, P.play_time
FROM PlayList P
JOIN Game G ON P.game_id = G.game_id
WHERE P.customer_id = '$user_id'
ORDER BY P.play_time DESC
";
$ret = mysqli_query($db, $sql);

echo "<H1>내 플레이리스트</H1>";
echo "<TABLE border=1>";
echo "<TR>
<TH>게임 이름</TH>
<TH>플레이타임(분)</TH>
</TR>";

while($row = mysqli_fetch_array($ret)) {
    echo "<TR>";
    echo "<TD>", htmlspecialchars($row['game_name']), "</TD>";
    echo "<TD>", $row['play_time'], "</TD>";
    echo "</TR>";
}
echo "</TABLE>";
echo "<BR><A HREF='user_main.php'>← 사용자 메인</A>";
mysqli_close($db);
?>

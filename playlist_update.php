<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$customer_id = $_GET['customer_id'];
$game_id = $_GET['game_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $play_time = $_POST['play_time'];
    $sql = "UPDATE PlayList SET play_time=$play_time WHERE customer_id=$customer_id AND game_id=$game_id";
    mysqli_query($db, $sql);
    mysqli_close($db);
    header("Location: playlist_manage.php");
    exit;
}
$row = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM PlayList WHERE customer_id=$customer_id AND game_id=$game_id"));
?>
<form method="post">
    플레이타임: <input name="play_time" value="<?=$row['play_time']?>"><br>
    <input type="submit" value="수정">
</form>
<a href="playlist_manage.php">← 플레이리스트 관리</a>

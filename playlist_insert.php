<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
    mysqli_set_charset($db, 'utf8');
    $customer_id = $_POST['customer_id'];
    $game_id = $_POST['game_id'];
    $play_time = $_POST['play_time'];
    $sql = "INSERT INTO PlayList (customer_id, game_id, play_time) VALUES ($customer_id, $game_id, $play_time)";
    mysqli_query($db, $sql);
    mysqli_close($db);
    header("Location: playlist_manage.php");
    exit;
}
?>
<form method="post">
    고객ID: <input name="customer_id"><br>
    게임ID: <input name="game_id"><br>
    플레이타임: <input name="play_time"><br>
    <input type="submit" value="추가">
</form>
<a href="playlist_manage.php">← 플레이리스트 관리</a>

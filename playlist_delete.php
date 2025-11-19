<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$customer_id = $_GET['customer_id'];
$game_id = $_GET['game_id'];
mysqli_query($db, "DELETE FROM PlayList WHERE customer_id=$customer_id AND game_id=$game_id");
mysqli_close($db);
header("Location: playlist_manage.php");
exit;
?>

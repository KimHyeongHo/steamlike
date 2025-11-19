<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$sale_id = $_GET['sale_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $game_id = $_POST['game_id'];
    $discount_rate = $_POST['discount_rate'];
    $discount_period = $_POST['discount_period'];
    $reason = $_POST['reason'];
    $sql = "UPDATE Sale SET game_id=$game_id, discount_rate=$discount_rate, discount_period='$discount_period', reason='$reason' WHERE sale_id=$sale_id";
    mysqli_query($db, $sql);
    mysqli_close($db);
    header("Location: sale_manage.php");
    exit;
}
$row = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM Sale WHERE sale_id=$sale_id"));
?>
<form method="post">
    게임ID: <input name="game_id" value="<?=$row['game_id']?>"><br>
    할인율: <input name="discount_rate" value="<?=$row['discount_rate']?>"><br>
    할인 기간: <input name="discount_period" value="<?=htmlspecialchars($row['discount_period'])?>"><br>
    사유: <input name="reason" value="<?=htmlspecialchars($row['reason'])?>"><br>
    <input type="submit" value="수정">
</form>
<a href="sale_manage.php">← 세일 관리</a>

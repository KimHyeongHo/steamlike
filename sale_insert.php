<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
    mysqli_set_charset($db, 'utf8');
    $sale_id = $_POST['sale_id'];
    $game_id = $_POST['game_id'];
    $discount_rate = $_POST['discount_rate'];
    $discount_period = $_POST['discount_period'];
    $reason = $_POST['reason'];

    // sale_id 중복 체크
    $check = mysqli_query($db, "SELECT sale_id FROM Sale WHERE sale_id = $sale_id");
    if (mysqli_fetch_assoc($check)) {
        echo "<script>alert('이미 존재하는 세일ID입니다. 다른 값을 입력하세요.');history.back();</script>";
        mysqli_close($db);
        exit;
    }

    $sql = "INSERT INTO Sale (sale_id, game_id, discount_rate, discount_period, reason) VALUES ($sale_id, $game_id, $discount_rate, '$discount_period', '$reason')";
    if (mysqli_query($db, $sql)) {
        mysqli_close($db);
        header("Location: sale_manage.php");
        exit;
    } else {
        echo "<script>alert('세일 추가 중 오류가 발생했습니다.');history.back();</script>";
        mysqli_close($db);
        exit;
    }
}
?>
<form method="post">
    세일ID: <input name="sale_id" required><br>
    게임ID: <input name="game_id" required><br>
    할인율: <input name="discount_rate" required><br>
    할인 기간(예: 2025-05-01 ~ 2025-05-07): <input name="discount_period" required><br>
    사유: <input name="reason"><br>
    <input type="submit" value="추가">
</form>
<a href="sale_manage.php">← 세일 관리</a>

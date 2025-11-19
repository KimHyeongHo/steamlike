<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
    mysqli_set_charset($db, 'utf8');
    $game_id = $_POST['game_id'];
    $name = $_POST['name'];
    $genre = $_POST['genre'];
    $developer = $_POST['developer'];
    $price = $_POST['price'];
    $rating = $_POST['rating'];
    $sql = "INSERT INTO Game (game_id, name, genre, developer, price, rating) VALUES ($game_id, '$name', '$genre', '$developer', $price, '$rating')";
    mysqli_query($db, $sql);
    mysqli_close($db);
    header("Location: game_manage.php");
    exit;
}
?>
<!-- 입력 폼 예시 -->
<form method="post">
    게임 ID: <input name="game_id"><br>
    이름: <input name="name"><br>
    장르: <input name="genre"><br>
    개발사: <input name="developer"><br>
    가격: <input name="price"><br>
    평점: <input name="rating"><br>
    <input type="submit" value="추가">
</form>
<a href="game_manage.php">← 게임 관리</a>

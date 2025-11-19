<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$customer_id = $_GET['customer_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nickname = $_POST['nickname'];
    $bank = $_POST['bank'];
    $language = $_POST['language'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $is_admin = $_POST['is_admin'];
    $sql = "UPDATE Customer SET nickname='$nickname', bank=$bank, language='$language', email='$email', password='$password', is_admin=$is_admin WHERE customer_id=$customer_id";
    mysqli_query($db, $sql);
    mysqli_close($db);
    header("Location: user_manage.php");
    exit;
}
$row = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM Customer WHERE customer_id=$customer_id"));
?>
<form method="post">
    닉네임: <input name="nickname" value="<?=htmlspecialchars($row['nickname'])?>"><br>
    잔액: <input name="bank" value="<?=$row['bank']?>"><br>
    언어: <input name="language" value="<?=htmlspecialchars($row['language'])?>"><br>
    이메일: <input name="email" value="<?=htmlspecialchars($row['email'])?>"><br>
    비밀번호: <input name="password" value="<?=htmlspecialchars($row['password'])?>"><br>
    관리자(1=관리자,0=유저): <input name="is_admin" value="<?=$row['is_admin']?>"><br>
    <input type="submit" value="수정">
</form>
<a href="user_manage.php">← 유저 관리</a>

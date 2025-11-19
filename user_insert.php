<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
    mysqli_set_charset($db, 'utf8');
    $customer_id = $_POST['customer_id'];
    $nickname = $_POST['nickname'];
    $bank = $_POST['bank'];
    $language = $_POST['language'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $is_admin = $_POST['is_admin'];
    $sql = "INSERT INTO Customer (customer_id, nickname, bank, language, email, password, is_admin) VALUES ($customer_id, '$nickname', $bank, '$language', '$email', '$password', $is_admin)";
    mysqli_query($db, $sql);
    mysqli_close($db);
    header("Location: user_manage.php");
    exit;
}
?>
<form method="post">
    ID: <input name="customer_id"><br>
    닉네임: <input name="nickname"><br>
    잔액: <input name="bank"><br>
    언어: <input name="language"><br>
    이메일: <input name="email"><br>
    비밀번호: <input name="password"><br>
    관리자(1=관리자,0=유저): <input name="is_admin"><br>
    <input type="submit" value="추가">
</form>
<a href="user_manage.php">← 유저 관리</a>

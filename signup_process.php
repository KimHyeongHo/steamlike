<?php
header("Content-Type:text/html;charset=utf-8");

$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

$customer_id = $_POST['customer_id'];
$nickname = $_POST['nickname'];
$password = $_POST['password'];
$email = $_POST['email'];

$sql = "INSERT INTO Customer (customer_id, nickname, bank, language, email, password, is_admin)
        VALUES ('$customer_id', '$nickname', 0, 'Korean', '$email', '$password', 0)";
$result = mysqli_query($db, $sql);

if ($result) {
    echo "<script>alert('회원가입이 완료되었습니다. 로그인해 주세요.');location.href='main.php';</script>";
} else {
    echo "<script>alert('회원가입 실패: 중복된 ID 또는 DB 오류');history.back();</script>";
}
mysqli_close($db);
?>

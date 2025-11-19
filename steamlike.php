<?php
session_start();
header("Content-Type:text/html;charset=utf-8");

// DB 접속
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

if (!$db) {
    echo "db 접속: 실패<br>";
    exit;
}

// 폼에서 입력받은 값 (ID: customer_id, PS: password)
$id = isset($_POST['id']) ? $_POST['id'] : '999'; // 숫자 ID
$password = isset($_POST['password']) ? $_POST['password'] : 'admin';

// 로그인 시도
$sql = "SELECT * FROM Customer WHERE customer_id='$id' AND password='$password'";
$result = mysqli_query($db, $sql);
$user = mysqli_fetch_assoc($result);

if ($user) {
    $_SESSION['user_id'] = $user['customer_id'];
    $_SESSION['nickname'] = $user['nickname'];
    $_SESSION['is_admin'] = $user['is_admin'];

    if ($user['is_admin'] == 1) {
        echo "관리자 로그인 성공!<br>";
		echo "현재 닉네임: " . $user['nickname'] . "<br>";
		echo "현재 ID: " . $user['customer_id'] . "<br>";
    } else {
        echo "사용자 로그인 성공!<br>";
		echo "현재 닉네임: " . $user['nickname'] . "<br>";
		echo "현재 ID: " . $user['customer_id'] . "<br>";
    }
} else {
    echo "로그인 실패: 아이디 또는 비밀번호가 올바르지 않습니다.<br>";
    echo "아이디: $id<br>";
    echo "비밀번호: $password<br>";
}

mysqli_close($db);
?>

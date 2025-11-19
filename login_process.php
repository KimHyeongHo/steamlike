<?php
session_start();
header("Content-Type:text/html;charset=utf-8");

$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

$id = isset($_POST['id']) ? $_POST['id'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// SQL 인젝션 방지를 위해 Prepared Statement 사용
$sql = "SELECT customer_id, nickname, is_admin FROM Customer WHERE customer_id=? AND password=?";
$stmt = mysqli_prepare($db, $sql);

// 파라미터 바인딩: s(string)
mysqli_stmt_bind_param($stmt, "ss", $id, $password); 
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($user) {
    $_SESSION['user_id'] = $user['customer_id'];
    $_SESSION['nickname'] = $user['nickname'];
    $_SESSION['is_admin'] = $user['is_admin'];
    if ($user['is_admin'] == 1) {
        header("Location: admin_main.php");
    } else {
        header("Location: user_main.php");
    }
    exit;
} else {
    echo "<script>alert('로그인 실패: 아이디 또는 비밀번호가 올바르지 않습니다.');history.back();</script>";
}
mysqli_close($db);
?>
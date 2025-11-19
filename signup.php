<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
    mysqli_set_charset($db, 'utf8');

    $customer_id = intval($_POST['customer_id']);
    $nickname = $_POST['nickname'];
    $email = $_POST['email'];
    $raw_password = $_POST['password'];
    $language = isset($_POST['language']) ? $_POST['language'] : 'Korean';
    $bank = 0.0;
    $hashed_password = $raw_password; // 평문 암호 사용 (DB 스키마 한계로 인해)

    // ------------------- 중복 체크 (Prepared Statement 적용) -------------------
    $sql_check = "SELECT 1 FROM Customer WHERE customer_id = ? OR email = ? OR nickname = ?";
    $stmt_check = mysqli_prepare($db, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "iss", $customer_id, $email, $nickname);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_fetch_assoc($result_check)) {
        echo "<script>alert('이미 사용 중인 ID, 이메일, 또는 닉네임입니다.');history.back();</script>";
        mysqli_stmt_close($stmt_check);
        mysqli_close($db);
        exit;
    }
    mysqli_stmt_close($stmt_check);

    // ------------------- 회원가입 (Prepared Statement 적용) -------------------
    $sql_insert = "INSERT INTO Customer (customer_id, nickname, bank, language, email, password, is_admin)
            VALUES (?, ?, ?, ?, ?, ?, 0)";
    $stmt_insert = mysqli_prepare($db, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "idsiss", $customer_id, $nickname, $bank, $language, $email, $hashed_password);

    if (mysqli_stmt_execute($stmt_insert)) {
        mysqli_stmt_close($stmt_insert);
        mysqli_close($db);
        echo "<script>alert('회원가입이 완료되었습니다.');location.href='main.html';</script>";
        exit;
    } else {
        echo "<script>alert('회원가입 중 오류가 발생했습니다.');history.back();</script>";
        mysqli_stmt_close($stmt_insert);
        mysqli_close($db);
        exit;
    }
}

// ------------------- AJAX 요청 처리: ID 중복 체크 (Prepared Statement 적용) -------------------
if (isset($_GET['check_id'])) {
    $db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
    mysqli_set_charset($db, 'utf8');
    $check_id = intval($_GET['check_id']);
    
    $sql_ajax = "SELECT 1 FROM Customer WHERE customer_id = ?";
    $stmt_ajax = mysqli_prepare($db, $sql_ajax);
    mysqli_stmt_bind_param($stmt_ajax, "i", $check_id);
    mysqli_stmt_execute($stmt_ajax);
    $result_ajax = mysqli_stmt_get_result($stmt_ajax);

    if (mysqli_fetch_assoc($result_ajax)) {
        echo "중복";
    } else {
        echo "사용 가능";
    }
    mysqli_stmt_close($stmt_ajax);
    mysqli_close($db);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    </head>
<body>
<h1>회원가입</h1>
<form method="post">
    </form>
<br>
<a href="main.html">← 메인으로</a>
</body>
</html>
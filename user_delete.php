<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$customer_id = $_GET['customer_id'];

// 트랜잭션 시작: 데이터 무결성 보장
mysqli_begin_transaction($db);
$success = true;

try {
    // 0. customer_id=9900 더미 유저가 없으면 생성
    $sql_dummy = "INSERT IGNORE INTO Customer (customer_id, nickname, bank, language, email, password, is_admin)
                  VALUES (9900, 'Deleted User', 0, 'Unknown', 'deleted@steam.com', 'deleted', 0)";
    if (!mysqli_query($db, $sql_dummy)) { throw new Exception("Dummy User Insert Fail"); }

    // 1. OrderList에서 외래키를 9900으로 변경 (구매 기록 보존)
    if (!mysqli_query($db, "UPDATE OrderList SET customer_id = 9900 WHERE customer_id = $customer_id")) { throw new Exception("OrderList Update Fail"); }
    
    // 2. PlayList에서 해당 유저의 기록 삭제
    if (!mysqli_query($db, "DELETE FROM PlayList WHERE customer_id = $customer_id")) { throw new Exception("PlayList Delete Fail"); }

    // 3. 부모 테이블에서 삭제
    if (!mysqli_query($db, "DELETE FROM Customer WHERE customer_id = $customer_id")) { throw new Exception("Customer Delete Fail"); }

    mysqli_commit($db); // 모든 단계 성공 시 최종 반영

} catch (Exception $e) {
    mysqli_rollback($db); // 오류 발생 시 이전 상태로 되돌림
    echo "<script>alert('유저 삭제 중 오류가 발생했습니다: ". $e->getMessage() ."');history.back();</script>";
    $success = false;
}

mysqli_close($db);
if ($success) {
    header("Location: user_manage.php");
    exit;
}
?>
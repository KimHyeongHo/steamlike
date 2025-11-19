<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$sale_id = $_GET['sale_id'];

// 1. 자식 테이블의 외래키를 NULL로 변경
mysqli_query($db, "UPDATE OrderList SET sale_id = NULL WHERE sale_id = $sale_id");

// 2. 부모 테이블에서 삭제
mysqli_query($db, "DELETE FROM Sale WHERE sale_id = $sale_id");

mysqli_close($db);
header("Location: sale_manage.php");
exit;
?>

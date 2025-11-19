<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$order_id = $_GET['order_id'];
mysqli_query($db, "DELETE FROM OrderList WHERE order_id=$order_id");
mysqli_close($db);
header("Location: orderlist_manage.php");
exit;
?>

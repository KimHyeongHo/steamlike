<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
    mysqli_set_charset($db, 'utf8');
    $order_id = $_POST['order_id'];
    $customer_id = $_POST['customer_id'];
    $game_id = $_POST['game_id'];
    $sale_id = $_POST['sale_id'];
    $payed_price = $_POST['payed_price'];
    $order_time = $_POST['order_time'];
    $payment_method = $_POST['payment_method'];
    $discount_rate = $_POST['discount_rate'];
    $sql = "INSERT INTO OrderList (order_id, customer_id, game_id, sale_id, payed_price, order_time, payment_method, discount_rate) VALUES ($order_id, $customer_id, $game_id, $sale_id, $payed_price, '$order_time', '$payment_method', $discount_rate)";
    mysqli_query($db, $sql);
    mysqli_close($db);
    header("Location: orderlist_manage.php");
    exit;
}
?>
<form method="post">
    주문ID: <input name="order_id"><br>
    고객ID: <input name="customer_id"><br>
    게임ID: <input name="game_id"><br>
    세일ID: <input name="sale_id"><br>
    결제금액: <input name="payed_price"><br>
    주문시간(YYYY-MM-DD HH:MM:SS): <input name="order_time"><br>
    결제수단: <input name="payment_method"><br>
    할인율: <input name="discount_rate"><br>
    <input type="submit" value="추가">
</form>
<a href="orderlist_manage.php">← 오더리스트 관리</a>

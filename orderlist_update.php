<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$old_order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_order_id = intval($_POST['order_id']); // 새로 입력받은 값
    $old_order_id = intval($_POST['old_order_id']); // 기존 값 (hidden)
    $customer_id = $_POST['customer_id'];
    $game_id = $_POST['game_id'];
    $sale_id = $_POST['sale_id'];
    $payed_price = $_POST['payed_price'];
    $order_time = $_POST['order_time'];
    $payment_method = $_POST['payment_method'];
    $discount_rate = $_POST['discount_rate'];

    // order_id가 변경될 경우 중복 체크
    if ($new_order_id != $old_order_id) {
        $check = mysqli_query($db, "SELECT 1 FROM OrderList WHERE order_id = $new_order_id");
        if (mysqli_fetch_assoc($check)) {
            echo "<script>alert('이미 존재하는 주문ID입니다.');history.back();</script>";
            mysqli_close($db);
            exit;
        }
    }

    $sql = "UPDATE OrderList SET 
        order_id=$new_order_id, 
        customer_id=$customer_id, 
        game_id=$game_id, 
        sale_id=$sale_id, 
        payed_price=$payed_price, 
        order_time='$order_time', 
        payment_method='$payment_method', 
        discount_rate=$discount_rate 
        WHERE order_id=$old_order_id";
    mysqli_query($db, $sql);
    mysqli_close($db);
    header("Location: orderlist_manage.php");
    exit;
}
$row = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM OrderList WHERE order_id=$old_order_id"));
?>
<form method="post">
    <input type="hidden" name="old_order_id" value="<?=$old_order_id?>">
    주문ID: <input name="order_id" value="<?=$row['order_id']?>"><br>
    고객ID: <input name="customer_id" value="<?=$row['customer_id']?>"><br>
    게임ID: <input name="game_id" value="<?=$row['game_id']?>"><br>
    세일ID: <input name="sale_id" value="<?=$row['sale_id']?>"><br>
    결제금액: <input name="payed_price" value="<?=$row['payed_price']?>"><br>
    주문시간: <input name="order_time" value="<?=$row['order_time']?>"><br>
    결제수단: <input name="payment_method" value="<?=htmlspecialchars($row['payment_method'])?>"><br>
    할인율: <input name="discount_rate" value="<?=$row['discount_rate']?>"><br>
    <input type="submit" value="수정">
</form>
<a href="orderlist_manage.php">← 오더리스트 관리</a>

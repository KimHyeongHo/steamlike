<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$id = $_SESSION['user_id'];

// 충전 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charge_amount'])) {
    $charge = floatval($_POST['charge_amount']);
    if ($charge > 0) {
        mysqli_query($db, "UPDATE Customer SET bank = bank + $charge WHERE customer_id = $id");
        echo "<script>alert('{$charge}원이 충전되었습니다.');location.href='user_edit.php';</script>";
        mysqli_close($db);
        exit;
    }
}

// 정보 수정 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nickname'])) {
    $nickname = $_POST['nickname'];
    $email = $_POST['email'];
    $sql = "UPDATE Customer SET nickname='$nickname', email='$email' WHERE customer_id='$id'";
    if (mysqli_query($db, $sql)) {
        echo "<script>alert('수정이 완료되었습니다.');location.href='user_edit.php';</script>";
        mysqli_close($db);
        exit;
    } else {
        echo "<script>alert('수정 중 오류가 발생했습니다.');history.back();</script>";
        mysqli_close($db);
        exit;
    }
}

// 현재 정보 조회
$sql = "SELECT * FROM Customer WHERE customer_id='$id'";
$ret = mysqli_query($db, $sql);
$row = mysqli_fetch_array($ret);

echo "<H1>내 정보 조회/수정</H1>";
echo "<form method='post'>";
echo "ID: <span style='font-weight:bold;'>".$row['customer_id']."</span><br>";
echo "닉네임: <input type='text' name='nickname' value='".htmlspecialchars($row['nickname'])."'><br>";
echo "이메일: <input type='email' name='email' value='".htmlspecialchars($row['email'])."'><br>";
echo "<br><input type='submit' value='수정'>";
echo "</form>";

echo "<hr style='margin:24px 0;'>";

// 보유 금액 및 충전 폼
echo "<b>현재 보유 금액: </b> <span style='color:blue; font-weight:bold;'>".number_format($row['bank'])." 원</span>";
echo "<form method='post' style='display:inline; margin-left:20px;'>";
echo "<input type='number' name='charge_amount' min='1' placeholder='충전 금액' style='width:100px;'> ";
echo "<input type='submit' value='충전'>";
echo "</form>";

echo "<br><br><a href='user_main.php'>← 사용자 메인</a>";
mysqli_close($db);
?>

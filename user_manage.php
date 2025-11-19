<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

// 전체 관리자 수 조회
$result = mysqli_query($db, "SELECT COUNT(*) AS admin_count FROM Customer WHERE is_admin = 1");
$row = mysqli_fetch_assoc($result);
$admin_count = $row['admin_count'];

$sql = "SELECT * FROM Customer";
$ret = mysqli_query($db, $sql);
echo "<H1>유저 관리</H1>";
echo "<A HREF='user_insert.php'>[유저 추가]</A><BR><BR>";
echo "<TABLE border=1>";
echo "<TR><TH>ID</TH><TH>닉네임</TH><TH>이메일</TH><TH>권한</TH><TH>수정</TH><TH>삭제</TH></TR>";
while($row = mysqli_fetch_array($ret)) {
    echo "<TR>";
    echo "<TD>", $row['customer_id'], "</TD>";
    echo "<TD>", $row['nickname'], "</TD>";
    echo "<TD>", $row['email'], "</TD>";
    echo "<TD>", $row['is_admin'], "</TD>";

    // 마지막 관리자라면 수정(권한변경) 불가 표시
    if ($row['is_admin'] == 1 && $admin_count <= 1) {
        echo "<TD>수정 불가 (마지막 관리자)</TD>";
    } else {
        echo "<TD><A HREF='user_update.php?customer_id=", $row['customer_id'], "'>수정</A></TD>";
    }

    // 마지막 관리자라면 삭제 불가 표시
    if ($row['is_admin'] == 1 && $admin_count <= 1) {
        echo "<TD>삭제 불가 (마지막 관리자)</TD>";
    } else {
        echo "<TD><A HREF='user_delete.php?customer_id=", $row['customer_id'], "'>삭제</A></TD>";
    }

    echo "</TR>";
}
echo "</TABLE>";
echo "<BR><A HREF='admin_main.php'>← 관리자 메인</A>";
mysqli_close($db);
?>

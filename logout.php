<?php
session_start();
session_unset();    // 모든 세션 변수 제거
session_destroy();  // 세션 자체 파괴

// 메인화면으로 이동
header("Location: main.html");
exit;
?>

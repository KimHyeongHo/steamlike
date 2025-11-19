<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] == 1) {
    header("Location: main.php");
    exit;
}
?>
<HTML>
<HEAD>
    <META http-equiv="content-type" content="text/html; charset=utf-8">
    <title>STEAM 사용자 페이지</title>
</HEAD>
<BODY>
    <H1>STEAM [사용자 페이지]</H1>
    <A HREF='game_search.php'>게임 검색</A><BR><BR>
    <A HREF='sale_search.php'>세일 검색</A><BR><BR>
    <A HREF='user_edit.php'>내 정보 수정/조회</A><BR><BR>
    <A HREF='orderlist_user.php'>주문 목록</A><BR><BR>
    <A HREF='playlist_user.php'>플레이리스트</A><BR><BR>
    <A HREF='logout.php'>로그아웃</A>
</BODY>
</HTML>

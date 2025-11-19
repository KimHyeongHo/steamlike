<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: main.php");
    exit;
}
?>
<HTML>
<HEAD>
    <META http-equiv="content-type" content="text/html; charset=utf-8">
    <title>STEAM 관리자 페이지</title>
</HEAD>
<BODY>
    <H1>STEAM [관리자 페이지]</H1>
    <A HREF='game_manage.php'>(1) 게임 관리(검색/수정/삭제/추가)</A><BR><BR>
    <A HREF='user_manage.php'>(2) 유저 관리(검색/수정/삭제/추가)</A><BR><BR>
    <A HREF='sale_manage.php'>(3) 세일 관리(검색/수정/삭제/추가)</A><BR><BR>
    <A HREF='orderlist_manage.php'>(4) 오더리스트 관리(검색/수정/삭제/추가)</A><BR><BR>
    <A HREF='playlist_manage.php'>(5) 플레이리스트 관리(검색/수정/삭제/추가)</A><BR><BR>
    <A HREF='logout.php'>로그아웃</A>
</BODY>
</HTML>

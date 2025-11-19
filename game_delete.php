<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');
$game_id = $_GET['game_id'];

// 트랜잭션 시작: 데이터 무결성 보장
mysqli_begin_transaction($db);
$success = true;

try {
    // 0. game_id=9999 더미 게임이 없으면 생성
    $sql_dummy = "INSERT IGNORE INTO Game (game_id, name, genre, developer, price, rating)
                  VALUES (9999, 'Deleted Game', 'Unknown', 'Unknown', 0, 'Mixed')";
    if (!mysqli_query($db, $sql_dummy)) { throw new Exception("Dummy Insert Fail"); }

    // 1. PlayList에서 해당 게임의 기록을 먼저 삭제
    if (!mysqli_query($db, "DELETE FROM PlayList WHERE game_id = $game_id")) { throw new Exception("PlayList Delete Fail"); }

    // 2. Sale, OrderList 등에서 외래키를 9999로 변경
    if (!mysqli_query($db, "UPDATE Sale SET game_id = 9999 WHERE game_id = $game_id")) { throw new Exception("Sale Update Fail"); }
    if (!mysqli_query($db, "UPDATE OrderList SET game_id = 9999 WHERE game_id = $game_id")) { throw new Exception("OrderList Update Fail"); }

    // 3. Game 테이블에서 게임 삭제
    if (!mysqli_query($db, "DELETE FROM Game WHERE game_id = $game_id")) { throw new Exception("Game Delete Fail"); }

    mysqli_commit($db); // 모든 단계 성공 시 최종 반영

} catch (Exception $e) {
    mysqli_rollback($db); // 오류 발생 시 이전 상태로 되돌림
    echo "<script>alert('게임 삭제 중 오류가 발생했습니다: ". $e->getMessage() ."');history.back();</script>";
    $success = false;
}

mysqli_close($db);
if ($success) {
    header("Location: game_manage.php");
    exit;
}
?>
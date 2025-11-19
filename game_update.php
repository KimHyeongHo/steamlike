<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) { header("Location: main.php"); exit; }
$db = mysqli_connect("localhost", "202101472user", "202101472pw", "steamdb");
mysqli_set_charset($db, 'utf8');

$old_game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_game_id = intval($_POST['new_game_id']);
    // SQL Injection 방지 최소화 (Prepared Statement 권장되지만, 기존 코드 유지 위해 escape string 사용)
    $name = mysqli_real_escape_string($db, $_POST['name']); 
    $genre = mysqli_real_escape_string($db, $_POST['genre']);
    $developer = mysqli_real_escape_string($db, $_POST['developer']);
    $price = $_POST['price'];
    $rating = mysqli_real_escape_string($db, $_POST['rating']);
    $old_game_id = intval($_POST['old_game_id']);

    // 트랜잭션 시작 (ID 변경 시 무결성 확보)
    mysqli_begin_transaction($db);
    $success = true;

    try {
        // 1. 게임ID가 변경되는 경우 (복잡한 다단계 처리)
        if ($old_game_id != $new_game_id) {
            // 새 game_id가 이미 존재하는지 체크
            $check = mysqli_query($db, "SELECT 1 FROM Game WHERE game_id = $new_game_id");
            if (mysqli_fetch_assoc($check)) {
                echo "<script>alert('이미 존재하는 게임ID입니다.');history.back();</script>";
                mysqli_close($db);
                exit; 
            }

            // 2. 새 game_id로 Game INSERT (기존 정보+수정 정보)
            $sql_insert = "INSERT INTO Game (game_id, name, genre, developer, price, rating)
                           VALUES ($new_game_id, '$name', '$genre', '$developer', $price, '$rating')";
            if (!mysqli_query($db, $sql_insert)) { throw new Exception("New Game Insert Fail"); }

            // 3. 자식 테이블의 FK 업데이트
            if (!mysqli_query($db, "UPDATE OrderList SET game_id = $new_game_id WHERE game_id = $old_game_id")) { throw new Exception("OrderList FK Update Fail"); }
            if (!mysqli_query($db, "UPDATE Sale SET game_id = $new_game_id WHERE game_id = $old_game_id")) { throw new Exception("Sale FK Update Fail"); }
            if (!mysqli_query($db, "UPDATE PlayList SET game_id = $new_game_id WHERE game_id = $old_game_id")) { throw new Exception("PlayList FK Update Fail"); }

            // 4. 기존 game_id의 Game 행 삭제
            if (!mysqli_query($db, "DELETE FROM Game WHERE game_id = $old_game_id")) { throw new Exception("Old Game Delete Fail"); }
            
        } else {
            // game_id가 안 바뀌면 그냥 UPDATE
            $sql = "UPDATE Game SET name='$name', genre='$genre', developer='$developer', price=$price, rating='$rating' WHERE game_id=$old_game_id";
            if (!mysqli_query($db, $sql)) { throw new Exception("Simple Update Fail"); }
        }

        mysqli_commit($db); // 모든 단계 성공 시 최종 반영

    } catch (Exception $e) {
        mysqli_rollback($db); // 오류 발생 시 이전 상태로 되돌림
        echo "<script>alert('게임 수정 중 오류가 발생했습니다: ". $e->getMessage() ."');history.back();</script>";
        $success = false;
    }

    mysqli_close($db);
    if ($success) {
        header("Location: game_manage.php");
        exit;
    }
}

// 수정 폼 출력 부분 (GET 요청)
$row = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM Game WHERE game_id=$old_game_id"));
?>
<form method="post">
    <input type="hidden" name="old_game_id" value="<?=$old_game_id?>">
    게임 ID: <input name="new_game_id" value="<?=$row['game_id']?>"><br>
    <input type="submit" value="수정">
</form>
<a href="game_manage.php">← 게임 관리</a>
<?php mysqli_close($db); ?>
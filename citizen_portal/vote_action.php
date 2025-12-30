<?php
session_start();
require '../db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_POST['complaint_id']) || !isset($_POST['type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}
$user_id = (int)$_SESSION['user_id'];
$complaint_id = (int)$_POST['complaint_id'];
$type = $_POST['type'];
if ($type !== 'up' && $type !== 'down') {
    echo json_encode(['success' => false, 'message' => 'Invalid vote type']);
    exit();
}
$vote_value = ($type === 'up') ? 1 : -1;
$check_sql = "SELECT vote_type FROM `complaint_votes` WHERE user_id = ? AND complaint_id = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ii", $user_id, $complaint_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $existing_vote = (int)$row['vote_type'];
    if ($existing_vote === $vote_value) {
        $del = $conn->prepare("DELETE FROM `complaint_votes` WHERE user_id = ? AND complaint_id = ?");
        $del->bind_param("ii", $user_id, $complaint_id);
        $del->execute();
    } else {
        $upd = $conn->prepare("UPDATE `complaint_votes` SET vote_type = ? WHERE user_id = ? AND complaint_id = ?");
        $upd->bind_param("iii", $vote_value, $user_id, $complaint_id);
        $upd->execute();
    }
} else {
    $ins = $conn->prepare("INSERT INTO `complaint_votes` (user_id, complaint_id, vote_type) VALUES (?, ?, ?)");
    $ins->bind_param("iii", $user_id, $complaint_id, $vote_value);
    $ins->execute();
}
$sum_sql = "SELECT COALESCE(SUM(vote_type), 0) AS total FROM `complaint_votes` WHERE complaint_id = ?";
$stmt_sum = $conn->prepare($sum_sql);
$stmt_sum->bind_param("i", $complaint_id);
$stmt_sum->execute();
$total = (int)$stmt_sum->get_result()->fetch_assoc()['total'];
echo json_encode(['success' => true, 'new_score' => $total]);

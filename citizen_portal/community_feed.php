<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: citizen_login.php");
    exit();
}
$my_id = (int)$_SESSION['user_id'];
// Gets complaint list complaint table, category, citizen name
$sql = "
    SELECT c.*,
           c.Complaint_id AS complaint_id,
           cat.name AS category_name,
           u.name AS citizen_name,
           COALESCE(
               (SELECT SUM(v2.vote_type)
                FROM complaint_votes v2
                WHERE v2.complaint_id = c.Complaint_id), 0
           ) AS vote_score,
           (SELECT v.vote_type
            FROM complaint_votes v
            WHERE v.complaint_id = c.Complaint_id AND v.user_id = ?) AS my_vote
    FROM complaint c
    LEFT JOIN complaint_category cat ON c.cat_id = cat.category_id
    JOIN user u ON c.user_id = u.user_id
    ORDER BY vote_score DESC, c.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $my_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community Issues - Poricchonota</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .nav-bar { margin-bottom: 20px; }
        .nav-bar a { text-decoration: none; color: #3498db; font-weight: bold; font-size: 16px; }

        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; gap: 20px; }
        .vote-box { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-width: 50px; }
        .vote-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: #ccc; transition: 0.2s; padding: 0; }
        .vote-btn:hover { color: #555; }
        .score { font-size: 18px; font-weight: bold; margin: 5px 0; color: #333; }
        .vote-btn.active-up { color: #e67e22; }
        .vote-btn.active-down { color: #7f8c8d; }
        .content-box { flex-grow: 1; }
        .meta { color: #7f8c8d; font-size: 13px; margin-bottom: 5px; }
        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; color: white; margin-left: 10px; }
        .Pending { background-color: #f39c12; }
        .In-Progress { background-color: #3498db; }
        .Resolved { background-color: #27ae60; }
        .Rejected { background-color: #e74c3c; }
        h3 { margin: 5px 0 10px 0; color: #2c3e50; }
        p { color: #555; line-height: 1.5; margin: 0 0 10px 0; }
        .location { font-size: 13px; color: #888; display: flex; align-items: center; }
        .thumb { width: 100px; height: 100px; object-fit: cover; border-radius: 5px; border: 1px solid #eee; }
    </style>
</head>
<body>
<div class="container">
    <div class="nav-bar">
        <a href="citizen_dashboard.php">&larr; Back to Dashboard</a>
        <h1 style="margin-top:10px;">Community Issues</h1>
        <p style="color:#666;">Top voted issues get priority attention.</p>
    </div>
    <?php while($row = $result->fetch_assoc()):
        $c_id    = (int)$row['complaint_id'];
        $votes   = (int)$row['vote_score'];
        $my_vote = $row['my_vote'] ?? 0;
        $up_class   = ((int)$my_vote === 1) ? 'active-up' : '';
        $down_class = ((int)$my_vote === -1) ? 'active-down' : '';
    ?>
    <div class="card">
        <div class="vote-box">
            <button class="vote-btn up <?php echo $up_class; ?>" onclick="vote(<?php echo $c_id; ?>, 'up')">▲</button>
            <span class="score" id="score-<?php echo $c_id; ?>"><?php echo $votes; ?></span>
            <button class="vote-btn down <?php echo $down_class; ?>" onclick="vote(<?php echo $c_id; ?>, 'down')">▼</button>
        </div>

        <div class="content-box">
            <div class="meta">
                <span><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></span>
                <span> • Posted by <?php echo htmlspecialchars($row['citizen_name']); ?></span>
                <span> • <?php echo date("d M", strtotime($row['created_at'])); ?></span>
                <span class="status-badge <?php echo str_replace(' ', '-', $row['status']); ?>">
                    <?php echo htmlspecialchars($row['status']); ?>
                </span>
            </div>

            <h3>
                <a href="view_complaint.php?id=<?php echo $c_id; ?>" style="text-decoration:none; color:inherit;">
                    <?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?>
                </a>
            </h3>

            <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
            <div class="location">📍 <?php echo htmlspecialchars($row['location']); ?></div>
        </div>

        <?php if(!empty($row['complaint_image'])): ?>
            <img src="../uploads/<?php echo htmlspecialchars($row['complaint_image']); ?>" class="thumb">
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
</div>
<script>
function vote(complaintId, type) {
    const formData = new FormData();
    formData.append('complaint_id', complaintId);
    formData.append('type', type);

    fetch('vote_action.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('score-' + complaintId).innerText = data.new_score;
            location.reload();
        } else {
            alert('Error voting: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => console.error(err));
}
</script>
</body>
</html>

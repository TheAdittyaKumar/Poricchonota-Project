<?php
require '../db.php';
$message = "";
$departments = [];
$dept_sql = "SELECT dept_id, name FROM `department`";
$result = $conn->query($dept_sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $phone    = trim($_POST['phone']);
    $nid      = trim($_POST['nid']);
    $role     = $_POST['role'];     
    $dept_id  = intval($_POST['dept_id']);

    if ($role !== 'engineer' && $role !== 'admin') {
        $message = "<p style='color:red;'>Error: Invalid role selected.</p>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $conn->begin_transaction();
        try {
            $sql_user = "INSERT INTO `user` (nid, name, email, password, phone, role)
                         VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_user);
            $stmt->bind_param("ssssss", $nid, $name, $email, $hashed_password, $phone, $role);

            if (!$stmt->execute()) {
                throw new Exception("Email or NID already exists.");
            }

            $new_user_id = $conn->insert_id;
            $sql_staff = "INSERT INTO `staff` (user_id, joined_at) VALUES (?, CURDATE())";
            $stmt2 = $conn->prepare($sql_staff);
            $stmt2->bind_param("i", $new_user_id);
            $stmt2->execute();
            if ($role === "engineer") {
                $sql_role = "INSERT INTO `engineer` (user_id, dept_id) VALUES (?, ?)";
            } else { 
                $sql_role = "INSERT INTO `admin` (user_id, dept_id) VALUES (?, ?)";
            }
            $stmt3 = $conn->prepare($sql_role);
            $stmt3->bind_param("ii", $new_user_id, $dept_id);
            $stmt3->execute();
            $conn->commit();
            $message = "<p style='color:green;'>Staff account created successfully! <a href='staff_login.php'>Login here</a></p>";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Signup</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background-color: #1a252f; }
        h2 { text-align: center; color: #333; margin-top: 0; }
    </style>
</head>
<body>

<div class="container">
    <h2>Create Staff Account</h2>
    <p style="text-align:center; font-size: 12px; color: #666;">(Internal Use Only)</p>
    <?php echo $message; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="nid" placeholder="National ID (NID)" required>
        <input type="email" name="email" placeholder="Official Email" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <label style="font-weight:bold; font-size:14px;">Role:</label>
        <select name="role" required>
            <option value="engineer">Field Engineer</option>
            <option value="admin">Department Admin</option>
        </select>
        <label style="font-weight:bold; font-size:14px;">Select Organization:</label>
        <select name="dept_id" required>
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo (int)$dept['dept_id']; ?>">
                    <?php echo htmlspecialchars($dept['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Create Account</button>
    </form>
    <div style="text-align:center; margin-top:15px; font-size:14px;">
        <a href="staff_login.php" style="color:#3498db; text-decoration:none;">Back to Login</a>
    </div>
</div>
</body>
</html>

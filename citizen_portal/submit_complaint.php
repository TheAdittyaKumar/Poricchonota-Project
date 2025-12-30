<?php
session_start();
require '../db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: citizen_login.php");
    exit();
}
$success_msg = "";
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = (int)$_SESSION['user_id'];
    $cat_id = (int)($_POST['cat_id'] ?? 0);

    $location_text = trim($_POST['location'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $latitude = trim($_POST['latitude'] ?? "");
    $longitude = trim($_POST['longitude'] ?? "");
    $title = ""; 
    $image_path = NULL;
    if (isset($_FILES['complaint_image']) && $_FILES['complaint_image']['error'] == 0) {
        $target_dir = "../uploads/";
        $file_name = time() . "_" . basename($_FILES["complaint_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($image_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["complaint_image"]["tmp_name"], $target_file)) {
                $image_path = $file_name;
            } else {
                $error_msg = "Failed to upload image.";
            }
        } else {
            $error_msg = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
    }

    if (empty($location_text) || empty($description) || empty($cat_id)) {
        $error_msg = "Please fill in all required fields.";
    } elseif (empty($error_msg)) {
        $sql = "
            INSERT INTO complaint
                (user_id, cat_id, title, description, location_text, location, latitude, longitude, complaint_image, status)
            VALUES
                (?, ?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, 'Pending')
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $error_msg = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param(
                "iisssssss",
                $user_id,
                $cat_id,
                $title,
                $description,
                $location_text,   
                $location_text,   
                $latitude,
                $longitude,
                $image_path
            );

            if ($stmt->execute()) {
                $success_msg = "Complaint submitted successfully!";
                header("refresh:2;url=citizen_dashboard.php");
            } else {
                $error_msg = "Error submitting: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
$categories = [];
$cat_sql = "SELECT category_id, name FROM complaint_category";
$result = $conn->query($cat_sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Complaint - Poricchonota</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f0f2f5; margin: 0; padding: 0; }
        .header { background-color: #009688; color: white; padding: 15px 40px; display: flex; align-items: center; }
        .back-btn { color: white; text-decoration: none; font-weight: bold; margin-right: 20px; font-size: 20px; }
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #009688; margin-top: 0; }
        label { display: block; margin-top: 15px; color: #555; font-weight: bold; }
        select, input[type="text"], textarea, input[type="file"] {
            width: 100%; padding: 12px; margin-top: 5px;
            border: 1px solid #ddd; border-radius: 5px;
            box-sizing: border-box; font-family: inherit;
        }
        #map { height: 300px; width: 100%; margin-top: 10px; border-radius: 5px; border: 2px solid #ddd; }
        button {
            width: 100%; padding: 14px; margin-top: 25px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white; border: none; border-radius: 5px;
            font-size: 16px; font-weight: bold; cursor: pointer;
        }
        button:hover { opacity: 0.9; }
        .msg { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<div class="header">
    <a href="citizen_dashboard.php" class="back-btn">&#8592;</a>
    <h3>Post a New Complaint</h3>
</div>
<div class="container">
    <h2>Report an Issue</h2>
    <p style="color:#777; margin-bottom:20px;">Provide details, a photo, and the location.</p>

    <?php if ($success_msg): ?>
        <div class="msg success"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php elseif ($error_msg): ?>
        <div class="msg error"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Category</label>
        <select name="cat_id" required>
            <option value="">-- Select Issue Type --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo (int)$cat['category_id']; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label>Title</label>
        <input type="text" name="title" placeholder="Short title (e.g. Broken Street Light)" required>
        <label>Description</label>
        <textarea name="description" rows="3" placeholder="Describe the issue in detail..." required></textarea>
        <label>Attach Photo (Optional)</label>
        <input type="file" name="complaint_image" accept="image/*">
        <label>Address / Landmark</label>
        <input type="text" name="location" placeholder="e.g. Near House 12, Road 5" required>
        <label>Pinpoint Location on Map (Click on the map)</label>
        <div id="map"></div>
        <input type="hidden" name="latitude" id="lat">
        <input type="hidden" name="longitude" id="lng">
        <button type="submit">Submit Complaint</button>
    </form>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([23.8103, 90.4125], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    var marker;
    map.on('click', function(e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
    });
</script>
</body>
</html>

<?php
// Database Connection
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = "";     // Replace with your database password
$dbname = "advertisement_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $targetDir = "uploads/"; // Ensure this directory exists and is writable
    $targetFile = $targetDir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if image file is an actual image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        $uploadOk = 0;
        $message = "File is not an image.";
    }

    // Check file size (5MB max)
    if ($_FILES["image"]["size"] > 5000000) {
        $uploadOk = 0;
        $message = "Sorry, your file is too large.";
    }

    // Allow certain file formats
    if (!in_array($imageFileType, array("jpg", "png", "jpeg", "gif"))) {
        $uploadOk = 0;
        $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk === 0) {
        $message = "Sorry, your file was not uploaded.";
    } else {
        // Try to upload file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            // Save image path in database
            $stmt = $conn->prepare("INSERT INTO images (image_path, division) VALUES (?, ?)");
            // Get division from the POST data
            $division = intval($_POST['division']); // Make sure to cast to int
            $stmt->bind_param("si", $targetFile, $division);
            $stmt->execute();
            $stmt->close();
            $message = "The file " . htmlspecialchars(basename($_FILES["image"]["name"])) . " has been uploaded.";
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }
}

// Handle image deletion
if (isset($_GET['delete'])) {
    $imageId = intval($_GET['delete']);
    $result = $conn->query("SELECT image_path FROM images WHERE id = $imageId");
    $row = $result->fetch_assoc();
    if ($row) {
        $imagePath = $row['image_path'];
        if (unlink($imagePath)) {
            // Delete from database
            $conn->query("DELETE FROM images WHERE id = $imageId");
            $message = "The image has been deleted.";
        } else {
            $message = "There was an error deleting the image.";
        }
    }
}

// Fetch images
$images = [];
$result = $conn->query("SELECT id, image_path, division FROM images");
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DigiBoard | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center">DigiBoard</h1>
    <p class="text-center">Coded by p.velante@gmail.com</p>
    <hr>
    <div class="card">
        <div class="card-header">
            <h3>Admin Panel</h3>
        </div>
        <div class="card-body">
            <!-- Image Upload Form -->
            <form action="" method="post" enctype="multipart/form-data" class="mt-4">
                <div class="mb-3">
                    <label for="image" class="form-label">Upload Image</label>
                    <input type="file" name="image" class="form-control" id="image" required>
                </div>
                <div class="mb-3">
                    <label for="division" class="form-label">Select Image Location</label>
                    <select name="division" class="form-select" id="division" required>
                        <option value="1">Top Left</option>
                        <option value="2">Bottom Left</option>
                        <option value="3">Right</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>

            <br>

            <!-- Display Images in a Bootstrap Table -->
            <h2>Existing Images</h2>
            <table class="table table-bordered" data-toggle="table" data-pagination="true" data-page-size="4"
                   data-search="false">
                <thead class="table-light">
                    <tr>
                        <th data-field="id">ID</th>
                        <th data-field="image">Image</th>
                        <th data-field="division">Division</th>
                        <th data-field="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($images as $image): ?>
                        <tr>
                            <td><?php echo $image['id']; ?></td>
                            <td>
                                <img src="<?php echo $image['image_path']; ?>" alt="Image" style="height: 100px; width: auto;">
                                <div style="font-size: 12px; color: #555; margin-top: 5px;"><?php echo htmlspecialchars(basename($image['image_path'])); ?></div>
                            </td>
                            <td>
                                <?php
                                switch ($image['division']) {
                                    case 1:
                                        echo "Top Left";
                                        break;
                                    case 2:
                                        echo "Bottom Left";
                                        break;
                                    case 3:
                                        echo "Right";
                                        break;
                                    default:
                                        echo "Unknown";
                                        break;
                                }
                                ?>
                            </td>
                            <td>
                                <a href="?delete=<?php echo $image['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this image?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Modal for Upload/Delete Notification -->
            <?php if (isset($message)): ?>
                <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="notificationModalLabel">Notification</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php echo $message; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
                    modal.show();
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/bootstrap-table/dist/bootstrap-table.min.js"></script>

</body>
</html>
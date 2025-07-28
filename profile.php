<?php 
// Fixed profile.php - Image upload with proper user linking and error handling
session_start();

// Get user ID from URL or session
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

if (!$userId) {
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registration_db";

$message = "";
$messageType = "";

// Create connection
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verify user exists
    $stmt = $pdo->prepare("SELECT id, applicant_name FROM info_user WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header("Location: index.php");
        exit();
    }
    
    // Check if image_store table exists and has correct structure
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'image_store'");
    if ($tableCheck->rowCount() == 0) {
        // Create the table if it doesn't exist
        $createTable = "CREATE TABLE image_store (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            image_name VARCHAR(255) NOT NULL,
            image_data LONGBLOB NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES info_user(id) ON DELETE CASCADE
        )";
        $pdo->exec($createTable);
    } else {
        // Check if user_id column exists
        $columnCheck = $pdo->query("SHOW COLUMNS FROM image_store LIKE 'user_id'");
        if ($columnCheck->rowCount() == 0) {
            // Add user_id column if it doesn't exist
            $pdo->exec("ALTER TABLE image_store ADD COLUMN user_id INT NOT NULL");
        }
    }
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $file = $_FILES['profile_image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes) && !in_array($file['type'], $allowedTypes)) {
            $message = "Only JPG/JPEG files are allowed.";
            $messageType = "error";
        } else {
            // Get image dimensions
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                $message = "Invalid image file.";
                $messageType = "error";
            } elseif ($imageInfo[0] < 400 || $imageInfo[1] < 500) {
                $message = "Image must be at least 400 x 500 pixels. Current size: " . $imageInfo[0] . " x " . $imageInfo[1];
                $messageType = "error";
            } else {
                // Read the image file
                $imageData = file_get_contents($file['tmp_name']);
                $imageName = $file['name'];
                
                try {
                    // Check if user already has an image
                    $checkStmt = $pdo->prepare("SELECT id FROM image_store WHERE user_id = ?");
                    $checkStmt->execute([$userId]);
                    $existingImage = $checkStmt->fetch();
                    
                    if ($existingImage) {
                        // Update existing image
                        $stmt = $pdo->prepare("UPDATE image_store SET image_name = ?, image_data = ?, uploaded_at = CURRENT_TIMESTAMP WHERE user_id = ?");
                        $stmt->bindParam(1, $imageName);
                        $stmt->bindParam(2, $imageData, PDO::PARAM_LOB);
                        $stmt->bindParam(3, $userId);
                    } else {
                        // Insert new image
                        $stmt = $pdo->prepare("INSERT INTO image_store (user_id, image_name, image_data) VALUES (?, ?, ?)");
                        $stmt->bindParam(1, $userId);
                        $stmt->bindParam(2, $imageName);
                        $stmt->bindParam(3, $imageData, PDO::PARAM_LOB);
                    }
                    
                    if ($stmt->execute()) {
                        $message = "Image uploaded successfully!";
                        $messageType = "success";
                        
                        // Set session variable for success page
                        $_SESSION['upload_success'] = true;
                        
                        // Auto redirect to download page after 2 seconds
                        echo "<script>
                            setTimeout(function() {
                                window.location.href = 'download.php?user_id=" . $userId . "&success=1';
                            }, 2000);
                        </script>";
                    } else {
                        $message = "Error uploading image.";
                        $messageType = "error";
                    }
                    
                } catch(PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $messageType = "error";
                    
                    // Debug information (remove in production)
                    error_log("Database error in profile.php: " . $e->getMessage());
                }
            }
        }
    } else {
        if (isset($_FILES['profile_image']['error'])) {
            switch ($_FILES['profile_image']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $message = "File is too large.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = "File upload was interrupted.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = "Please select an image file.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = "Temporary folder is missing.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = "Failed to write file to disk.";
                    break;
                default:
                    $message = "Unknown upload error.";
                    break;
            }
        } else {
            $message = "Please select an image file.";
        }
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form - Profile</title>
    <link rel="stylesheet" href="assests/css/profile.css">
 
</head>
<body>
    <div class="page-layout">
        <div class="container">
            <div class="header">
                <h1>Registration Form</h1>
                <p>Step 2: Upload Your Profile Picture</p>
            </div>

            <div class="user-info">
                <h3>Welcome, <?php echo htmlspecialchars($user['applicant_name']); ?>!</h3>
                <div class="user-id">User ID: <?php echo $userId; ?></div>
            </div>

            <div class="step-indicator">
                <div class="step completed">
                    <div class="step-number">✓</div>
                    <div class="step-text">Registration</div>
                </div>
                <div class="progress-line"></div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <div class="step-text">Profile</div>
                </div>
                <div class="progress-line inactive"></div>
                <div class="step inactive">
                    <div class="step-number">3</div>
                    <div class="step-text">Download</div>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="upload-section">
                    <h2>Upload Your Picture</h2>
                    <p>Please upload a passport-size photograph following the requirements below</p>
                    
                    <div class="requirements">
                        <p>Passport Size Picture is required; <span class="red-text">Fancy or other type of Picture is not Acceptable</span></p>
                        <div class="requirement-boxes">
                            <div class="requirement-box">Picture Size at least 400 x 500 Pixels or Greater</div>
                            <div class="requirement-box">Picture type must be JPG/JPEG</div>
                        </div>
                    </div>

                    <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                        <div class="upload-icon" id="uploadIcon">👤</div>
                        <img id="imagePreview" class="upload-preview" style="display: none;" alt="Preview">
                    </div>
                    
                    <input type="file" id="fileInput" name="profile_image" class="file-input" accept="image/jpeg,image/jpg" onchange="previewImage(event)" required>
                    
                    <div class="file-selection">
                        <button type="button" class="upload-btn" onclick="document.getElementById('fileInput').click()">
                            📁 Choose File
                        </button>
                        <span class="file-name" id="fileName">No file chosen</span>
                    </div>
                </div>
                
                <div class="navigation">
                    <a href="index.php" class="btn btn-back">
                        ← Back
                    </a>
                    <button type="submit" name="submit" class="btn btn-submit">
                        Upload Image
                    </button>
                    <?php if ($messageType == 'success'): ?>
                    <a href="download.php?user_id=<?php echo $userId; ?>&success=1" class="btn btn-continue">
                        Continue →
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

  
</body>
<script src="assests/javascript/profile.js"></script>
</html>
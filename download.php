<?php
// Fixed download.php - Works without mPDF library
session_start();

// Check if this is a download request
if (isset($_GET['action']) && $_GET['action'] === 'download_html') {
    // Generate printable HTML version
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "registration_db";

    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Fixed SQL query - removed u.created_at and fixed image join
        $sql = "SELECT 
                    u.id,
                    u.applicant_name,
                    u.s_w_d_name,
                    u.nationality,
                    u.city,
                    u.identification_type,
                    u.identification_no,
                    u.identification_of,
                    u.contact,
                    u.email,
                    u.religion,
                    u.d_o_b,
                    u.gender,
                    u.martial_s,
                    i.image_name,
                    i.image_data
                FROM info_user u
                LEFT JOIN image_store i ON u.id = i.user_id
                WHERE u.id = :user_id
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$userData) {
            die("User not found");
        }
        
        // Process image data if exists
        $imageData = '';
        if (!empty($userData['image_data'])) {
            $imageData = 'data:image/jpeg;base64,' . base64_encode($userData['image_data']);
        }
        
        // Generate HTML content for printing
        $html = generatePrintableHTML($userData, $imageData);
        
        // Set headers for download
        $filename = 'registration_form_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $userData['applicant_name']) . '_' . date('Y-m-d') . '.html';
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $html;
        exit;
        
    } catch(PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Function to generate printable HTML
function generatePrintableHTML($userData, $imageData = '') {
    // Since created_at doesn't exist, use current date
    $registrationDate = date('d/m/Y');

    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Registration Form - ' . htmlspecialchars($userData['applicant_name']) . '</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.4; 
            color: #000; 
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header { 
            background-color: #2c5f5f; 
            color: white; 
            padding: 20px; 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .header h1 { margin: 0 0 10px 0; font-size: 24px; }
        .header .tagline { font-size: 14px; font-style: italic; margin: 5px 0; }
        .header .contact { font-size: 12px; margin-top: 10px; }
        .form-title { 
            text-align: center; 
            background-color: #f0f0f0; 
            padding: 15px; 
            font-size: 18px; 
            font-weight: bold; 
            border: 1px solid #ccc; 
            margin-bottom: 20px; 
        }
        .top-section {
            display: grid;
            grid-template-columns: 1fr 150px;
            gap: 20px;
            margin-bottom: 20px;
        }
        .photo-section { 
            border: 2px solid #000; 
            width: 140px; 
            height: 160px; 
            text-align: center; 
            background-color: #f9f9f9; 
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .photo-section img { max-width: 130px; max-height: 150px; object-fit: cover; }
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .info-item { 
            margin-bottom: 12px; 
            padding: 8px; 
            border-bottom: 1px solid #ddd; 
        }
        .info-label { 
            font-weight: bold; 
            display: inline-block; 
            width: 150px; 
            color: #2c5f5f;
        }
        .info-value { 
            display: inline-block; 
            border-bottom: 1px solid #333; 
            min-width: 200px; 
            padding: 2px 5px; 
        }
        .section-header { 
            background-color: #2c5f5f; 
            color: white; 
            padding: 10px 15px; 
            font-weight: bold; 
            font-size: 14px; 
            margin: 20px 0 15px 0; 
        }
        .declaration {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            font-size: 11px;
            line-height: 1.5;
        }
        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 40px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            height: 40px;
            margin-bottom: 5px;
        }
        .print-btn {
            background-color: #2c5f5f;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin: 20px 0;
            font-size: 14px;
            border-radius: 4px;
        }
    </style>
    <script>
        function printForm() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="header">
       <img src="GiL.jpg" width="20px" height="20px">
        <h1>Government Innovation Lab (GIL)</h1>
        <div class="tagline">Forever Learning, Through Knowledge and Hardwork</div>
        <div class="contact">www.gil.edu.pk | +92 0833 500903 | admissions@gil.edu.pk</div>
    </div>
    
    <div class="form-title">Registration Application Form</div>
    
    <div class="top-section">
        <div class="user-info">
            <div class="info-item">
                <span class="info-label">User ID:</span>
                <span class="info-value">' . htmlspecialchars($userData['id'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Registration Date:</span>
                <span class="info-value">' . $registrationDate . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value">Active</span>
            </div>
        </div>
        <div class="photo-section">';
        
    if (!empty($imageData)) {
        $html .= '<img src="' . $imageData . '" alt="User Photo">';
    } else {
        $html .= '<div style="font-size: 11px; color: #666;">PHOTOGRAPH</div>';
    }
    
    $html .= '</div>
    </div>
    
    <div class="section-header">Personal Information</div>
    <div class="info-section">
        <div>
            <div class="info-item">
                <span class="info-label">Applicant Name:</span>
                <span class="info-value">' . htmlspecialchars($userData['applicant_name'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">S/D/W of:</span>
                <span class="info-value">' . htmlspecialchars($userData['s_w_d_name'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Birth Date:</span>
                <span class="info-value">' . htmlspecialchars($userData['d_o_b'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Gender:</span>
                <span class="info-value">' . htmlspecialchars($userData['gender'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Religion:</span>
                <span class="info-value">' . htmlspecialchars($userData['religion'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Marital Status:</span>
                <span class="info-value">' . htmlspecialchars($userData['martial_s'] ?? '') . '</span>
            </div>
        </div>
        <div>
            <div class="info-item">
                <span class="info-label">CNIC/Passport:</span>
                <span class="info-value">' . htmlspecialchars($userData['identification_no'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Nationality:</span>
                <span class="info-value">' . htmlspecialchars($userData['nationality'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">City:</span>
                <span class="info-value">' . htmlspecialchars($userData['city'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Mobile:</span>
                <span class="info-value">' . htmlspecialchars($userData['contact'] ?? '') . '</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">' . htmlspecialchars($userData['email'] ?? '') . '</span>
            </div>
        </div>
    </div>
    
    <div class="declaration">
        <h3>Declaration</h3>
        <p>I solemnly declare that the information submitted above is complete and true to the best of my knowledge. I understand that any misstatement or omission of material facts will disqualify me from registration or admission.</p>
        
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong>Applicant Signature</strong>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong>Date: ' . date('d/m/Y') . '</strong>
            </div>
        </div>
    </div>
    
    <div class="no-print">
        <button class="print-btn" onclick="printForm()">Print This Form</button>
    </div>
</body>
</html>';
    
    return $html;
}

// Main success page code
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$success = isset($_GET['success']) ? true : false;

if (!$userId) {
    header("Location: index.php");
    exit;
}

// Database connection to get user data for display
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registration_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT applicant_name FROM info_user WHERE id = :user_id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        header("Location: index.php");
        exit;
    }
    
} catch(PDOException $e) {
    $userData = ['applicant_name' => 'User'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - GIL</title>
   <link rel="stylesheet" href="assests/css//download.css">
</head>
<body>
    <div class="container">
        <div class="success-animation">
            <svg class="checkmark" viewBox="0 0 24 24" fill="none">
                <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        
        <div class="step-indicator">
            <div class="step">
                <div class="step-number">✓</div>
                <div class="step-text">Registration</div>
            </div>
            <div class="progress-line"></div>
            <div class="step">
                <div class="step-number">✓</div>
                <div class="step-text">Profile</div>
            </div>
            <div class="progress-line"></div>
            <div class="step">
                <div class="step-number">✓</div>
                <div class="step-text">Complete</div>
            </div>
        </div>
        
        <h1 class="success-title">Registration Complete!</h1>
        <h2 class="success-subtitle">Welcome, <?php echo htmlspecialchars($userData['applicant_name']); ?>!</h2>
        <p class="success-description">
            Your registration has been processed successfully. Your User ID is 
            <span class="user-id-display"><?php echo $userId; ?></span>
            <br>You can now download your registration form or view it online.
        </p>

        <div class="button-group">
            <button class="btn btn-primary" onclick="downloadHTML()">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="7,10 12,15 17,10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Download Form
            </button>
            
            <button class="btn btn-secondary" onclick="viewForm()">
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/>
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                </svg>
                View Online
            </button>
        </div>

        <div class="info-card">
            <div class="info-title">Important Information</div>
            <div class="info-text">
                • Please save your User ID <strong><?php echo $userId; ?></strong> for future reference<br>
                • Keep your registration form for your records<br>
                • You can print the form directly from the downloaded file<br>
                • Contact us if you need any assistance: admissions@gil.edu.pk
            </div>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="index.php" class="btn btn-secondary" style="width: auto; padding: 12px 24px;">
                Register Another User
            </a>
        </div>
    </div>

    <script>
        const userId = <?php echo $userId; ?>;
        
        function downloadHTML() {
            // Download the printable HTML version
            const downloadUrl = `download.php?action=download_html&user_id=${userId}`;
            window.location.href = downloadUrl;
        }

        function viewForm() {
            // Open the printable form in a new tab
            const viewUrl = `printform.php?user_id=${userId}`;
            window.open(viewUrl, '_blank');
        }
    </script>
</body>
</html>
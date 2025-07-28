<?php
// Fixed index.php - Registration form with proper redirection
session_start();

// Initialize error variables
$applicant_nameERR = $s_w_d_nameERR = $nationalityERR = $cityERR = $identification_typeERR = $identification_noERR = $identification_ofERR = $contactERR = $emailERR = $religionERR = $d_o_bERR = $passERR = $repeat_passERR = $genderERR = $martial_sERR = "";

// Initialize form variables
$applicant_name = $s_w_d_name = $nationality = $city = $identification_type = $identification_no = $identification_of = $contact = $email = $religion = $d_o_b = $pass = $repeat_pass = $gender = $martial_s = "";

$hasError = false;

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    /* Applicant Name validation */   
    if(empty($_POST["applicant_name"])){
        $applicant_nameERR = "Applicant name is required";
        $hasError = true;
    } else {
        $applicant_name = test_input($_POST["applicant_name"]);
        if (!preg_match("/^[a-zA-Z-' ]*$/", $applicant_name)) {
            $applicant_nameERR = "Only letters and white space allowed";
            $hasError = true;
        }
    }

    /* Parent Name validation */   
    if(empty($_POST["father_name"])){
        $s_w_d_nameERR = "S/D/W of is required";
        $hasError = true;
    } else {
        $s_w_d_name = test_input($_POST["father_name"]);
        if (!preg_match("/^[a-zA-Z-' ]*$/", $s_w_d_name)) {
            $s_w_d_nameERR = "Only letters and white space allowed";
            $hasError = true;
        }
    }

    /* Contact validation */   
    if(empty($_POST["contact"])){
        $contactERR = "Contact number is required";
        $hasError = true;
    } else {
        $contact = test_input($_POST["contact"]);
        if (!preg_match("/^[0-9]{11}$/", $contact)) {
            $contactERR = "Phone number must be 11 digits";
            $hasError = true;
        }
    }

    /* Email validation */
    if(empty($_POST["email"])){
        $emailERR = "Email address is required";
        $hasError = true;
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailERR = "Invalid email format";
            $hasError = true;
        }
    }

    /* Password validation */
    if(empty($_POST["password"]) || empty($_POST["repeat_password"])){
        $passERR = "Password is required";
        $hasError = true;
    } else {
        $pass = $_POST["password"];
        $repeat_pass = $_POST["repeat_password"];

        $pass_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

        if ($pass != $repeat_pass) {
            $repeat_passERR = "Passwords do not match";
            $hasError = true;
        } elseif (!preg_match($pass_pattern, $pass)) {
            $passERR = "Password must have at least 8 characters with minimum 1 uppercase, 1 lowercase, 1 number and 1 special character";
            $hasError = true;
        }
    }

    /* Nationality validation */
    if(empty($_POST["nationality"])){
        $nationalityERR = "Nationality is required";
        $hasError = true;
    } else {
        $nationality = test_input($_POST["nationality"]);
    }

    /* City validation */
    if(empty($_POST["city"])){
        $cityERR = "City/District is required";
        $hasError = true;
    } else {
        $city = test_input($_POST["city"]);
    }

    /* Identification number validation */
    if(empty($_POST["identification_number"])){
        $identification_noERR = "Identification number is required";
        $hasError = true;
    } else {
        $identification_no = test_input($_POST["identification_number"]);
        if (!preg_match("/^[0-9]{13}$/", $identification_no)) {
            $identification_noERR = "ID number must be 13 digits";
            $hasError = true;
        }
    }

    /* Identification type validation */
    if(empty($_POST["id_type"])){
        $identification_typeERR = "Identification type is required";
        $hasError = true;
    } else {
        $identification_type = test_input($_POST["id_type"]);
    }

    /* Identification of validation */
    if(empty($_POST["id_number_of"])){
        $identification_ofERR = "Identification number of is required";
        $hasError = true;
    } else {
        $identification_of = test_input($_POST["id_number_of"]);
    }

    /* Religion validation */
    if(empty($_POST["religion"])){
        $religionERR = "Religion is required";
        $hasError = true;
    } else {
        $religion = test_input($_POST["religion"]);
    }

    /* Date of birth validation */
    if(empty($_POST["date_of_birth"])){
        $d_o_bERR = "Date of birth is required";
        $hasError = true;
    } else {
        $d_o_b = test_input($_POST["date_of_birth"]);
    }

    /* Gender validation */
    if(empty($_POST["gender"])){
        $genderERR = "Gender is required";
        $hasError = true;
    } else {
        $gender = test_input($_POST["gender"]);
    }

    /* Marital status validation */
    if(empty($_POST["marital_status"])){
        $martial_sERR = "Marital status is required";
        $hasError = true;
    } else {
        $martial_s = test_input($_POST["marital_status"]);
    }

    /* Storing Data */
    if (!$hasError) {
        // Create database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $db_name = "registration_db";

        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$db_name", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Hash the password for security
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO info_user(applicant_name, nationality, s_w_d_name, city, identification_type, religion, identification_no, contact, identification_of, email, d_o_b, pass, gender, repeat_pass, martial_s) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            
            if ($stmt->execute([$applicant_name, $nationality, $s_w_d_name, $city, $identification_type, $religion, $identification_no, $contact, $identification_of, $email, $d_o_b, $hashed_password, $gender, $repeat_pass, $martial_s])) {
                // Get the inserted user ID
                $userId = $pdo->lastInsertId();
                
                // Store user ID in session
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $applicant_name;
                
                // Redirect to profile page
                header("Location: profile.php?user_id=" . $userId);
                exit();
            } else {
                echo "<script type='text/javascript'>alert('Error: Registration failed')</script>";
            }
            
        } catch(PDOException $e) {
            echo "<script type='text/javascript'>alert('Database Error: " . $e->getMessage() . "')</script>";
        }
    }
}

function test_input($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form - GIL</title>
    <link rel="shortcut icon" href="assests/img/add.png" type="image/x-icon">
   <link rel="stylesheet" href="assests/css/index.css">
</head>
<body>
    <div class="main-container">
        <!-- Left side with background image and overlay text -->
        <div class="left-side">
            <div class="overlay">
                <div class="overlay-content">
                    <h1>Your Monday Sign Up Now</h1>
                    <p>Get your best experience!</p>
                </div>
            </div>
        </div>

        <!-- Right side with form -->
        <div class="right-side">
            <!-- Progress bar -->
            <div class="progress-container">
                <div class="progress-step active">
                    <div class="step-number">1</div>
                    <span>Registration</span>
                </div>
                <div class="progress-line active"></div>
                <div class="progress-step">
                    <div class="step-number">2</div>
                    <span>Profile</span>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step">
                    <div class="step-number">3</div>
                    <span>Download</span>
                </div>
            </div>

            <!-- Form -->
            <form method="POST" class="registration-form">
                <div class="form-grid">
                    <!-- Row 1 -->
                    <div class="form-group">
                        <input type="text" name="applicant_name" value="<?php echo htmlspecialchars($applicant_name); ?>" placeholder="Applicant Name" class="<?php echo !empty($applicant_nameERR) ? 'error-input' : ''; ?>">
                        <?php if(!empty($applicant_nameERR)): ?>
                            <div class="error-message"><?php echo $applicant_nameERR; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <select name="nationality" class="<?php echo !empty($nationalityERR) ? 'error-input' : ''; ?>">
                            <option value="">Select Your Nationality</option>
                            <option value="pakistan" <?php echo ($nationality == 'pakistan') ? 'selected' : ''; ?>>Pakistan</option>
                            <option value="india" <?php echo ($nationality == 'india') ? 'selected' : ''; ?>>India</option>
                            <option value="uk" <?php echo ($nationality == 'uk') ? 'selected' : ''; ?>>UK</option>
                        </select>
                        <?php if(!empty($nationalityERR)): ?>
                            <div class="error-message"><?php echo $nationalityERR; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Row 2 -->
                    <div class="form-group">
                        <input type="text" name="father_name" value="<?php echo htmlspecialchars($s_w_d_name); ?>" placeholder="S/D/W of" class="<?php echo !empty($s_w_d_nameERR) ? 'error-input' : ''; ?>">
                        <?php if(!empty($s_w_d_nameERR)): ?>
                            <div class="error-message"><?php echo $s_w_d_nameERR; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <select name="city" class="<?php echo !empty($cityERR) ? 'error-input' : ''; ?>">
                            <option value="">Select your City/District</option>
                            <option value="sibi" <?php echo ($city == 'sibi') ? 'selected' : ''; ?>>Sibi</option>
                            <option value="quetta" <?php echo ($city == 'quetta') ? 'selected' : ''; ?>>Quetta</option>
                            <option value="karachi" <?php echo ($city == 'karachi') ? 'selected' : ''; ?>>Karachi</option>
                            <option value="lahore" <?php echo ($city == 'lahore') ? 'selected' : ''; ?>>Lahore</option>
                            <option value="islamabad" <?php echo ($city == 'islamabad') ? 'selected' : ''; ?>>Islamabad</option>
                        </select>
                        <?php if(!empty($cityERR)): ?>
                            <div class="error-message"><?php echo $cityERR; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Row 3 -->
                    <div class="form-group">
                        <select name="id_type" class="<?php echo !empty($identification_typeERR) ? 'error-input' : ''; ?>">
                            <option value="">Type of identification number</option>
                            <option value="b_form" <?php echo ($identification_type == 'b_form') ? 'selected' : ''; ?>>B-Form</option>
                            <option value="cnic" <?php echo ($identification_type == 'cnic') ? 'selected' : ''; ?>>CNIC</option>
                        </select>
                        <?php if(!empty($identification_typeERR)): ?>
                            <div class="error-message"><?php echo $identification_typeERR; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <select name="religion" class="<?php echo !empty($religionERR) ? 'error-input' : ''; ?>">
                            <option value="">Select Religion</option>
                            <option value="islam" <?php echo ($religion == 'islam') ? 'selected' : ''; ?>>Islam</option>
                            <option value="hindu" <?php echo ($religion == 'hindu') ? 'selected' : ''; ?>>Hindu</option>
                            <option value="christian" <?php echo ($religion == 'christian') ? 'selected' : ''; ?>>Christian</option>
                            <option value="other" <?php echo ($religion == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <?php if(!empty($religionERR)): ?>
                            <div class="error-message"><?php echo $religionERR; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Row 4 -->
                    <div class="form-group">
                        <input type="number" name="identification_number" value="<?php echo htmlspecialchars($identification_no); ?>" placeholder="Identification Number" class="<?php echo !empty($identification_noERR) ? 'error-input' : ''; ?>">
                        <?php if(!empty($identification_noERR)): ?>
                            <div class="error-message"><?php echo $identification_noERR; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="contact" value="<?php echo htmlspecialchars($contact); ?>" placeholder="Contact No" class="<?php echo !empty($contactERR) ? 'error-input' : ''; ?>">
                        <?php if(!empty($contactERR)): ?>
                            <div class="error-message"><?php echo $contactERR; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Row 5 -->
                    <div class="form-group">
                        <select name="id_number_of" class="<?php echo !empty($identification_ofERR) ? 'error-input' : ''; ?>">
                            <option value="">Identification number of</option>
                            <option value="own" <?php echo ($identification_of == 'own') ? 'selected' : ''; ?>>Own</option>
                            <option value="father" <?php echo ($identification_of == 'father') ? 'selected' : ''; ?>>Father</option>
                            <option value="mother" <?php echo ($identification_of == 'mother') ? 'selected' : ''; ?>>Mother</option>
                            <option value="husband" <?php echo ($identification_of == 'husband') ? 'selected' : ''; ?>>Husband</option>
                        </select>
                        <?php if(!empty($identification_ofERR)): ?>
                            <div class="error-message"><?php echo $identification_ofERR; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email Address" class="<?php echo !empty($emailERR) ? 'error-input' : ''; ?>">
                        <?php if(!empty($emailERR)): ?>
                            <div class="error-message"><?php echo $emailERR; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Row 6 -->
                    <div class="form-group">
                        <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($d_o_b); ?>" placeholder="mm/dd/yyyy" class="<?php echo !empty($d_o_bERR) ? 'error-input' : ''; ?>">
                        <?php if(!empty($d_o_bERR)): ?>
                            <div class="error-message"><?php echo $d_o_bERR; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password" class="<?php echo !empty($passERR) ? 'error-input' : ''; ?>">
                        <?php if(!empty($passERR)): ?>
                            <div class="error-message"><?php echo $passERR; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Row 7 -->
                    <div class="form-group">
                        <select name="gender" class="<?php echo !empty($genderERR) ? 'error-input' : ''; ?>">
                            <option value="">Gender</option>
                            <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo ($gender == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <?php if(!empty($genderERR)): ?>
                            <div class="error-message"><?php echo $genderERR; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <input type="password" name="repeat_password" placeholder="Repeat Password" class="<?php echo !empty($repeat_passERR) ? 'error-input' : ''; ?>">
                        <?php if(!empty($repeat_passERR)): ?>
                            <div class="error-message"><?php echo $repeat_passERR; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Row 8 -->
                    <div class="form-group">
                        <select name="marital_status" class="<?php echo !empty($martial_sERR) ? 'error-input' : ''; ?>">
                            <option value="">Marital Status</option>
                            <option value="single" <?php echo ($martial_s == 'single') ? 'selected' : ''; ?>>Single</option>
                            <option value="married" <?php echo ($martial_s == 'married') ? 'selected' : ''; ?>>Married</option>
                            <option value="divorced" <?php echo ($martial_s == 'divorced') ? 'selected' : ''; ?>>Divorced</option>
                            <option value="widowed" <?php echo ($martial_s == 'widowed') ? 'selected' : ''; ?>>Widowed</option>
                        </select>
                        <?php if(!empty($martial_sERR)): ?>
                            <div class="error-message"><?php echo $martial_sERR; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group"></div> <!-- Empty for grid alignment -->
                </div>

                <div class="navigation">
                    <a href="#" class="btn btn-back" onclick="return false;">
                        ← Back
                    </a>
                    <button type="submit" class="btn btn-submit">
                        Register & Continue →c  
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
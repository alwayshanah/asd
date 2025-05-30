<?php

$fullName = $email = $licenseNumber = $employeeId = $contactNumber = "";
$dateOfBirth = $passportNumber = $studentId = $tin = $plateNumber = "";
$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Full Name
    if (empty($_POST["fullName"])) {
        $errors["fullName"] = "Full name is required";
    } else {
        $fullName = test_input($_POST["fullName"]);
        if (!preg_match('/^[A-Za-z]+(?: [A-Za-z]+)* [A-Za-z]\. [A-Za-z]+$/', $fullName)) {
            $errors["fullName"] = "Invalid format. Example: Mark F. Cruz or Mary Jane F. Cruz";
        }
    }

    // Validate Email
    if (empty($_POST["email"])) {
        $errors["email"] = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "Invalid email format";
        }
    }

    // Validate License Number
    if (empty($_POST["licenseNumber"])) {
        $errors["licenseNumber"] = "License number is required";
    } else {
        $licenseNumber = test_input($_POST["licenseNumber"]);
        if (!preg_match('/^[A-Z]{3}-\d{4}-\d{4}$/', $licenseNumber)) {
            $errors["licenseNumber"] = "Invalid format. Example: PRC-2020-1234";
        }
    }

    // Validate Employee 
    if (empty($_POST["employeeId"])) {
        $errors["employeeId"] = "Employee ID is required";
    } else {
        $employeeId = test_input($_POST["employeeId"]);
        if (!preg_match('/^[A-Z]{3}-\d{4}-\d{5}$/', $employeeId)) {
            $errors["employeeId"] = "Invalid format. Example: ECO-2022-04567";
        }
    }

    // Validate Contact Number
    if (empty($_POST["contactNumber"])) {
        $errors["contactNumber"] = "Contact number is required";
    } else {
        $contactNumber = test_input($_POST["contactNumber"]);
        if (!preg_match('/^\+\d{1,3} \d{10}$/', $contactNumber)) {
            $errors["contactNumber"] = "Invalid format. Example: +63 9123456789";
        }
    }

    // Validate Date of Birth
    if (empty($_POST["dateOfBirth"])) {
        $errors["dateOfBirth"] = "Date of birth is required";
    } else {
        $dateOfBirth = test_input($_POST["dateOfBirth"]);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth)) {
            $errors["dateOfBirth"] = "Invalid format. Example: 1995-08-15";
        } else {
            // Validate Date
            $date_parts = explode('-', $dateOfBirth);
            if (!checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
                $errors["dateOfBirth"] = "Invalid date";
            }
        }
    }

    // Validate Passport Number
    if (empty($_POST["passportNumber"])) {
        $errors["passportNumber"] = "Passport number is required";
    } else {
        $passportNumber = test_input($_POST["passportNumber"]);
        if (!preg_match('/^[A-Z]\d{7}$/', $passportNumber)) {
            $errors["passportNumber"] = "Invalid format. Example: P1234567";
        }
    }

    // Validate Student ID
    if (empty($_POST["studentId"])) {
        $errors["studentId"] = "Student ID is required";
    } else {
        $studentId = test_input($_POST["studentId"]);
        if (!preg_match('/^\d{4}-[A-Za-z]+-\d{5}$/', $studentId)) {
            $errors["studentId"] = "Invalid format. Example: 2023-USeP-54321";
        }
    }

    // Validate TIN
    if (empty($_POST["tin"])) {
        $errors["tin"] = "TIN is required";
    } else {
        $tin = test_input($_POST["tin"]);
        if (!preg_match('/^\d{3}-\d{3}-\d{3}$/', $tin)) {
            $errors["tin"] = "Invalid format. Example: 123-456-789";
        }
    }

    // Validate Plate Number
    if (empty($_POST["plateNumber"])) {
        $errors["plateNumber"] = "Plate number is required";
    } else {
        $plateNumber = test_input($_POST["plateNumber"]);
        if (!preg_match('/^[A-Z]{3} \d{4}$/', $plateNumber)) {
            $errors["plateNumber"] = "Invalid format. Example: ABC 1234";
        }
    }

    if (empty($errors)) {
        $success = true;
    }
}

function test_input($data) {
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
    <title>User Data Validation Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="email"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            background-color: lightgray;
            color: black;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success h3 {
            color: blue;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .result-container {
            margin-top: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 4px;
        }
        .result-item {
            margin-bottom: 10px;
        }
        .hint {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Data Validation Form</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <h3>Form submitted successfully!</h3>
                <div class="result-container">
                    <div class="result-item"><strong>Full Name:</strong> <?php echo $fullName; ?></div>
                    <div class="result-item"><strong>Email Address:</strong> <?php echo $email; ?></div>
                    <div class="result-item"><strong>License Number:</strong> <?php echo $licenseNumber; ?></div>
                    <div class="result-item"><strong>Employee ID:</strong> <?php echo $employeeId; ?></div>
                    <div class="result-item"><strong>Contact Number:</strong> <?php echo $contactNumber; ?></div>
                    <div class="result-item"><strong>Date of Birth:</strong> <?php echo $dateOfBirth; ?></div>
                    <div class="result-item"><strong>Passport Number:</strong> <?php echo $passportNumber; ?></div>
                    <div class="result-item"><strong>Student ID:</strong> <?php echo $studentId; ?></div>
                    <div class="result-item"><strong>TIN:</strong> <?php echo $tin; ?></div>
                    <div class="result-item"><strong>Vehicle Plate Number:</strong> <?php echo $plateNumber; ?></div>
                </div>
            </div>
        <?php else: ?>
            <form id="userDataForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>
                <div class="form-group">
                    <label for="fullName">1. Full Name:</label>
                    <input type="text" id="fullName" name="fullName" value="<?php echo $fullName; ?>" placeholder="Mark Francis S. Cruz">
                    <div class="hint">Format: First Name <space> Middle Initial <dot> <space> Last Name</div>
                    <?php if (isset($errors["fullName"])): ?>
                        <div class="error"><?php echo $errors["fullName"]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">2. Email Address:</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" placeholder="mark.s.cruz@example.com">
                    <div class="hint">Format: local-part@domain.com</div>
                    <?php if (isset($errors["email"])): ?>
                        <div class="error"><?php echo $errors["email"]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="licenseNumber">3. License Number:</label>
                    <input type="text" id="licenseNumber" name="licenseNumber" value="<?php echo $licenseNumber; ?>" placeholder="PRC-2020-1234">
                    <div class="hint">Format: LLL = License Type, YYYY = Year of Issuance, NNNN = Serial Number)</div>
                    <?php if (isset($errors["licenseNumber"])): ?>
                        <div class="error"><?php echo $errors["licenseNumber"]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="employeeId">4. Employee ID:</label>
                    <input type="text" id="employeeId" name="employeeId" value="<?php echo $employeeId; ?>" placeholder="ECO-2022-04567">
                    <div class="hint">Format: CMP = Company Code, YYYY = Year, NNNNN = Employee Number)</div>
                    <?php if (isset($errors["employeeId"])): ?>
                        <div class="error"><?php echo $errors["employeeId"]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="contactNumber">5. Contact Number:</label>
                    <input type="text" id="contactNumber" name="contactNumber" value="<?php echo $contactNumber; ?>" placeholder="+63 9123456789">
                    <div class="hint">Format: + Country Code <space> 10-digit Number</div>
                    <?php if (isset($errors["contactNumber"])): ?>
                        <div class="error"><?php echo $errors["contactNumber"]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="dateOfBirth">6. Date of Birth:</label>
                    <input type="text" id="dateOfBirth" name="dateOfBirth" value="<?php echo $dateOfBirth; ?>" placeholder="1995-08-15">
                    <div class="hint">Format: YYYY-MM-DD</div>
                    <?php if (isset($errors["dateOfBirth"])): ?>
                        <div class="error"><?php echo $errors["dateOfBirth"]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="passportNumber">7. Passport Number:</label>
                    <input type="text" id="passportNumber" name="passportNumber" value="<?php echo $passportNumber; ?>" placeholder="P1234567">
                    <div class="hint">Format: Letter Prefix + 7 Digits</div>
                    <?php if (isset($errors["passportNumber"])): ?>
                        <div class="error"><?php echo $errors["passportNumber"]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="studentId">8. Student ID:</label>
                    <input type="text" id="studentId" name="studentId" value="<?php echo $studentId; ?>" placeholder="2023-USeP-54321">
                    <div class="hint">Format: YYYY = Year, SCH = School Code, NNNNN = Student Number)</div>
                    <?php if (isset($errors["studentId"])): ?>
                        <div class="error"><?php echo $errors["studentId"]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="tin">9. Tax Identification Number (TIN):</label>
                    <input type="text" id="tin" name="tin" value="<?php echo $tin; ?>" placeholder="123-456-789">
                    <div class="hint">Format: 999-999-999</div>
                    <?php if (isset($errors["tin"])): ?>
                        <div class="error"><?php echo $errors["tin"]; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="plateNumber">10. Vehicle Plate Number:</label>
                    <input type="text" id="plateNumber" name="plateNumber" value="<?php echo $plateNumber; ?>" placeholder="ABC 1234">
                    <div class="hint">Format: LLL 9999</div>
                    <?php if (isset($errors["plateNumber"])): ?>
                        <div class="error"><?php echo $errors["plateNumber"]; ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="submit-btn">Submit Form</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('userDataForm');
            
            form.addEventListener('submit', function(event) {
                let hasErrors = false;
                
                // Validate Full Name 
                const fullName = document.getElementById('fullName').value.trim();
                if (!fullName) {
                    showError('fullName', 'Full name is required');
                    hasErrors = true;
                } else if (!/^[A-Za-z]+(?: [A-Za-z]+)* [A-Za-z]\. [A-Za-z]+$/.test(fullName)) {
                    showError('fullName', 'Invalid format. Example: Mark Francis F. Cruz');
                    hasErrors = true;
                } else {
                    clearError('fullName');
                }
                
                // Validate Email
                const email = document.getElementById('email').value.trim();
                if (!email) {
                    showError('email', 'Email is required');
                    hasErrors = true;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showError('email', 'Invalid email format. Example: mark.s.cruz@example.com');
                    hasErrors = true;
                } else {
                    clearError('email');
                }
                
                // Validate License Number
                const licenseNumber = document.getElementById('licenseNumber').value.trim();
                if (!licenseNumber) {
                    showError('licenseNumber', 'License number is required');
                    hasErrors = true;
                } else if (!/^[A-Z]{3}-\d{4}-\d{4}$/.test(licenseNumber)) {
                    showError('licenseNumber', 'Invalid format. Example: PRC-2020-1234');
                    hasErrors = true;
                } else {
                    clearError('licenseNumber');
                }
                
                // Validate Employee ID
                const employeeId = document.getElementById('employeeId').value.trim();
                if (!employeeId) {
                    showError('employeeId', 'Employee ID is required');
                    hasErrors = true;
                } else if (!/^[A-Z]{3}-\d{4}-\d{5}$/.test(employeeId)) {
                    showError('employeeId', 'Invalid format. Example: ECO-2022-04567');
                    hasErrors = true;
                } else {
                    clearError('employeeId');
                }
                
                // Validate Contact Number
                const contactNumber = document.getElementById('contactNumber').value.trim();
                if (!contactNumber) {
                    showError('contactNumber', 'Contact number is required');
                    hasErrors = true;
                } else if (!/^\+\d{1,3} \d{10}$/.test(contactNumber)) {
                    showError('contactNumber', 'Invalid format. Example: +63 9123456789');
                    hasErrors = true;
                } else {
                    clearError('contactNumber');
                }
                
                // Validate Date 
                const dateOfBirth = document.getElementById('dateOfBirth').value.trim();
                if (!dateOfBirth) {
                    showError('dateOfBirth', 'Date of birth is required');
                    hasErrors = true;
                } else if (!/^\d{4}-\d{2}-\d{2}$/.test(dateOfBirth)) {
                    showError('dateOfBirth', 'Invalid format. Example: 1995-08-15');
                    hasErrors = true;
                } else {
                    // Validate date is real
                    const parts = dateOfBirth.split('-');
                    const year = parseInt(parts[0], 10);
                    const month = parseInt(parts[1], 10);
                    const day = parseInt(parts[2], 10);
                    
                    const date = new Date(year, month - 1, day);
                    if (date.getFullYear() !== year || date.getMonth() + 1 !== month || date.getDate() !== day) {
                        showError('dateOfBirth', 'Invalid date');
                        hasErrors = true;
                    } else {
                        clearError('dateOfBirth');
                    }
                }
                
                // Validate Passport Number
                const passportNumber = document.getElementById('passportNumber').value.trim();
                if (!passportNumber) {
                    showError('passportNumber', 'Passport number is required');
                    hasErrors = true;
                } else if (!/^[A-Z]\d{7}$/.test(passportNumber)) {
                    showError('passportNumber', 'Invalid format. Example: P1234567');
                    hasErrors = true;
                } else {
                    clearError('passportNumber');
                }
                
                // Validate Student ID
                const studentId = document.getElementById('studentId').value.trim();
                if (!studentId) {
                    showError('studentId', 'Student ID is required');
                    hasErrors = true;
                } else if (!/^\d{4}-[A-Za-z]+-\d{5}$/.test(studentId)) {
                    showError('studentId', 'Invalid format. Example: 2023-USeP-54321');
                    hasErrors = true;
                } else {
                    clearError('studentId');
                }
                
                // Validate TIN
                const tin = document.getElementById('tin').value.trim();
                if (!tin) {
                    showError('tin', 'TIN is required');
                    hasErrors = true;
                } else if (!/^\d{3}-\d{3}-\d{3}$/.test(tin)) {
                    showError('tin', 'Invalid format. Example: 123-456-789');
                    hasErrors = true;
                } else {
                    clearError('tin');
                }
                
                // Validate Plate Number
                const plateNumber = document.getElementById('plateNumber').value.trim();
                if (!plateNumber) {
                    showError('plateNumber', 'Plate number is required');
                    hasErrors = true;
                } else if (!/^[A-Z]{3} \d{4}$/.test(plateNumber)) {
                    showError('plateNumber', 'Invalid format. Example: ABC 1234');
                    hasErrors = true;
                } else {
                    clearError('plateNumber');
                }
                
                // Prevent form submission if there are errors
                if (hasErrors) {
                    event.preventDefault();
                }
            });
            
            function showError(fieldId, message) {
                const field = document.getElementById(fieldId);
                let errorDiv = field.parentNode.querySelector('.error');
                
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'error';
                    field.parentNode.appendChild(errorDiv);
                }
                
                errorDiv.textContent = message;
                field.style.borderColor = 'red';
            }
            
            function clearError(fieldId) {
                const field = document.getElementById(fieldId);
                const errorDiv = field.parentNode.querySelector('.error');
                
                if (errorDiv) {
                    errorDiv.remove();
                }
                
                field.style.borderColor = '';
            }
        });
    </script>
</body>
</html>
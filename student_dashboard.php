<?php
session_start();
include "db.php";

if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['student_name'];
$regno = $_SESSION['reg_no'];

// Fetch full student details
$sql = "SELECT * FROM students WHERE reg_no=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $regno);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>College Portal - Student Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-graduation-cap fa-2x"></i>
                <span class="ms-2">College Portal</span>
            </div>
            
            <div class="px-3">
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white border-0 active">
                        <i class="fas fa-home me-2"></i> Dashboard
                    </a>
                    <a href="study_plan.php" class="list-group-item list-group-item-action bg-transparent text-white border-0">
                        <i class="fas fa-book-open me-2"></i> Studyplanner
                    </a>
                    <a href="login.php" class="list-group-item list-group-item-action bg-transparent text-white border-0">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="welcome-header">
                <h4>Welcome Student: <?php echo $student['student_name']; ?> (Reg No: <?php echo $student['reg_no']; ?>)</h4>
                <p class="mb-0">Here's your academic overview</p>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-dashboard">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stats-number"><?php echo $student['marks']; ?></div>
                            <div class="stats-label">Marks</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card card-dashboard">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stats-number"><?php echo $student['attendance']; ?>%</div>
                            <div class="stats-label">Attendance</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card card-dashboard">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stats-number risk-<?php echo strtolower($student['risk_level']); ?>"><?php echo $student['risk_level']; ?></div>
                            <div class="stats-label">Risk Level</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card card-dashboard">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="stats-number"><?php echo $student['staff_name']; ?></div>
                            <div class="stats-label">Staff Advisor</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card card-dashboard">
                <div class="card-header">
                    <h5 class="mb-0">Your Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><b>Email:</b> <?php echo $student['gmail']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><b>Staff Name:</b> <?php echo $student['staff_name']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
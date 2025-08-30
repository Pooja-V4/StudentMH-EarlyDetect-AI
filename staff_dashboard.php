<?php
session_start();
include "db.php";

if ($_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

$staff_name = $_SESSION['staff_name'];
$department = $_SESSION['department'];

$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Get student data
$sql = "SELECT * FROM students WHERE staff_name=?";
if ($sort == 'risk_high') {
    $sql .= " ORDER BY 
        CASE risk_level 
            WHEN 'High' THEN 1 
            WHEN 'Moderate' THEN 2 
            WHEN 'Low' THEN 3 
            ELSE 4 
        END";
} elseif ($sort == 'risk_low') {
    $sql .= " ORDER BY 
        CASE risk_level 
            WHEN 'Low' THEN 1 
            WHEN 'Moderate' THEN 2 
            WHEN 'High' THEN 3 
            ELSE 4 
        END";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $staff_name);
$stmt->execute();
$result = $stmt->get_result();

// Calculate stats for the dashboard
$total_students = $result->num_rows;
$high_risk_count = 0;
$moderate_risk_count = 0;
$low_risk_count = 0;
$avg_marks = 0;
$avg_attendance = 0;

if ($total_students > 0) {
    $marks_sum = 0;
    $attendance_sum = 0;
    
    while ($row = $result->fetch_assoc()) {
        $marks_sum += $row['marks'];
        $attendance_sum += $row['attendance'];
        if ($row['risk_level'] == 'High') {
            $high_risk_count++;
        } elseif ($row['risk_level'] == 'Moderate') {
            $moderate_risk_count++;
        } else {
            $low_risk_count++;
        }
    }
    
    $avg_marks = round($marks_sum / $total_students, 1);
    $avg_attendance = round($attendance_sum / $total_students, 1);
    
    // Reset pointer for later use
    $result->data_seek(0);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>College Portal - Staff Dashboard</title>
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
                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white border-0">
                        <i class="fas fa-users me-2"></i> Students
                    </a>
                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white border-0">
                        <i class="fas fa-book me-2"></i> Courses
                    </a>
                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white border-0">
                        <i class="fas fa-chart-line me-2"></i> Results
                    </a>
                    <a href="login.php" class="list-group-item list-group-item-action bg-transparent text-white border-0">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="welcome-header">
                <h4>Welcome Staff: <?php echo $staff_name; ?></h4>
                <p class="mb-0">Department: <?php echo $department; ?></p>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-dashboard">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_students; ?></div>
                            <div class="stats-label">Total Students</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card card-dashboard">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stats-number"><?php echo $avg_marks; ?>%</div>
                            <div class="stats-label">Avg. Marks</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card card-dashboard">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stats-number"><?php echo $avg_attendance; ?>%</div>
                            <div class="stats-label">Avg. Attendance</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card card-dashboard">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stats-number"><?php echo $high_risk_count; ?></div>
                            <div class="stats-label">High Risk Students</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                    <div class="risk-summary">
                        <span class="risk-badge badge-high"><i class="fas fa-exclamation-circle me-1"></i> High Risk: <?php echo $high_risk_count; ?></span>
                        <span class="risk-badge badge-moderate"><i class="fas fa-info-circle me-1"></i> Moderate Risk: <?php echo $moderate_risk_count; ?></span>
                        <span class="risk-badge badge-low"><i class="fas fa-check-circle me-1"></i> Low Risk: <?php echo $low_risk_count; ?></span>
                    </div>
                    
                    <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Reg No</th>
                                    <th>Name</th>
                                    <th>Marks</th>
                                    <th>Attendance</th>
                                    <th>Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): 
                                    $risk_class = '';
                                    if ($row['risk_level'] == 'High') {
                                        $risk_class = 'risk-high';
                                    } elseif ($row['risk_level'] == 'Moderate') {
                                        $risk_class = 'risk-moderate';
                                    } else {
                                        $risk_class = 'risk-low';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $row['reg_no']; ?></td>
                                    <td><?php echo $row['student_name']; ?></td>
                                    <td><?php echo $row['marks']; ?></td>
                                    <td><?php echo $row['attendance']; ?>%</td>
                                    <td class="<?php echo $risk_class; ?>">
                                        <i class="fas 
                                            <?php 
                                            if ($row['risk_level'] == 'High') echo 'fa-exclamation-circle';
                                            elseif ($row['risk_level'] == 'Moderate') echo 'fa-info-circle';
                                            else echo 'fa-check-circle';
                                            ?> 
                                            me-1"></i>
                                        <?php echo $row['risk_level']; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-center">No students assigned yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
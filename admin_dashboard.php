<?php
session_start();
include "db.php";

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'];

// Get student data
$sql_students = "SELECT * FROM students";
$result_students = $conn->query($sql_students);

// Get staff data
$sql_staff = "SELECT * FROM staff";
$result_staff = $conn->query($sql_staff);

// Calculate stats for the dashboard
$total_students = $result_students->num_rows;
$total_staff = $result_staff->num_rows;
$high_risk_count = 0;
$avg_marks = 0;
$avg_attendance = 0;

if ($total_students > 0) {
    $marks_sum = 0;
    $attendance_sum = 0;
    
    while ($row = $result_students->fetch_assoc()) {
        $marks_sum += $row['marks'];
        $attendance_sum += $row['attendance'];
        if ($row['risk_level'] == 'High') {
            $high_risk_count++;
        }
    }
    
    $avg_marks = round($marks_sum / $total_students, 1);
    $avg_attendance = round($attendance_sum / $total_students, 1);
    
    // Reset pointer for later use
    $result_students->data_seek(0);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>College Portal - Admin Dashboard</title>
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
                        <i class="fas fa-user-tie me-2"></i> Staff
                    </a>
                    <a href="#" class="list-group-item list-group-item-action bg-transparent text-white border-0">
                        <i class="fas fa-cog me-2"></i> Settings
                    </a>
                    <a href="login.php" class="list-group-item list-group-item-action bg-transparent text-white border-0">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="welcome-header">
                <h4>Welcome Admin: <?php echo $admin_name; ?></h4>
                <p class="mb-0">College Administration Dashboard</p>
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
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="stats-number"><?php echo $total_staff; ?></div>
                            <div class="stats-label">Total Staff</div>
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
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="stats-number"><?php echo $high_risk_count; ?></div>
                            <div class="stats-label">High Risk Students</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-dashboard">
                        <div class="card-header">
                            <h5 class="mb-0">All Students</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($result_students->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Reg No</th>
                                            <th>Name</th>
                                            <th>Staff</th>
                                            <th>Marks</th>
                                            <th>Attendance</th>
                                            <th>Email</th>
                                            <th>Risk Level</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result_students->fetch_assoc()): 
                                            $risk_class = '';
                                            if ($row['risk_level'] == 'High') {
                                                $risk_class = 'risk-high';
                                            } elseif ($row['risk_level'] == 'Medium') {
                                                $risk_class = 'risk-medium';
                                            } else {
                                                $risk_class = 'risk-low';
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo $row['reg_no']; ?></td>
                                            <td><?php echo $row['student_name']; ?></td>
                                            <td><?php echo $row['staff_name']; ?></td>
                                            <td><?php echo $row['marks']; ?></td>
                                            <td><?php echo $row['attendance']; ?>%</td>
                                            <td><?php echo $row['gmail']; ?></td>
                                            <td class="<?php echo $risk_class; ?>"><?php echo $row['risk_level']; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-center">No students found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card card-dashboard">
                        <div class="card-header">
                            <h5 class="mb-0">All Staff</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($result_staff->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result_staff->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo $row['staff_name']; ?></td>
                                            <td><?php echo $row['email']; ?></td>
                                            <td><?php echo $row['department']; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-center">No staff found.</p>
                            <?php endif; ?>
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
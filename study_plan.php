<?php
session_start();
include "db.php";

if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['student_name'];
$regno = $_SESSION['reg_no'];

$sql = "SELECT * FROM students WHERE reg_no=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $regno);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$risk_level_map = [
    'Low' => 'low',
    'Moderate' => 'medium',
    'High' => 'high'
];

$risk_level = isset($risk_level_map[$student['risk_level']]) ? $risk_level_map[$student['risk_level']] : 'low';


$table_check = $conn->query("SHOW TABLES LIKE 'student_subjects'");
if ($table_check->num_rows == 0) {
    $create_table = "CREATE TABLE student_subjects (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        reg_no INT(11) NOT NULL,
        subject_name VARCHAR(255) NOT NULL,
        color VARCHAR(7) DEFAULT '#4e73df',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (reg_no) REFERENCES students(reg_no) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_table)) {
        // Insert some default subjects
        $default_subjects = [
            ['Mathematics', '#4e73df'],
            ['Science', '#6f42c1'],
            ['English', '#1cc88a']
        ];
        
        foreach ($default_subjects as $subject) {
            $insert_sql = "INSERT INTO student_subjects (reg_no, subject_name, color) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iss", $regno, $subject[0], $subject[1]);
            $insert_stmt->execute();
        }
    }
}

$student_subjects = [];
$subjects_sql = "SELECT subject_name, color FROM student_subjects WHERE reg_no=?";
$subjects_stmt = $conn->prepare($subjects_sql);

if ($subjects_stmt) {
    $subjects_stmt->bind_param("i", $regno);
    $subjects_stmt->execute();
    $subjects_result = $subjects_stmt->get_result();
    
    while ($row = $subjects_result->fetch_assoc()) {
        $student_subjects[] = $row;
    }
} else {
    $student_subjects = [
        ['subject_name' => 'Mathematics', 'color' => '#4e73df'],
        ['subject_name' => 'Science', 'color' => '#6f42c1'],
        ['subject_name' => 'English', 'color' => '#1cc88a']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentMH-EarlyAlert-AI - Adaptive Study Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href= "css/style.css">
    <link rel="stylesheet" href= "css/animations.css">
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1><i class="fas fa-brain me-2"></i>StudentMH-EarlyAlert-AI</h1>
            <p class="mb-0">Adaptive Study Plan Generator Based on Risk Level</p>
        </div>
        
        <div class="content">
            <div class="ai-recommendation">
                <div class="d-flex align-items-center">
                    <i class="fas fa-robot fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-1">AI-Powered Study Plan</h5>
                        <p class="mb-0">Our algorithm creates personalized study plans based on your academic performance and risk level</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-body">
                            <h4 class="card-title">Student Information</h4>
                            <form id="studentForm">
                                <div class="mb-3">
                                    <label class="form-label">Student Name</label>
                                    <input type="text" class="form-control" id="studentName" value="<?php echo $student['student_name']; ?>" required readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Registration Number</label>
                                    <input type="text" class="form-control" id="regNo" value="<?php echo $student['reg_no']; ?>" required readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Risk Level</label>
                                    <select class="form-select" id="riskLevel" required>
                                        <option value="low" <?php echo $risk_level == 'low' ? 'selected' : ''; ?>>Low Risk</option>
                                        <option value="medium" <?php echo $risk_level == 'medium' ? 'selected' : ''; ?>>Medium Risk</option>
                                        <option value="high" <?php echo $risk_level == 'high' ? 'selected' : ''; ?>>High Risk</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Marks</label>
                                    <input type="number" class="form-control" id="marks" min="0" max="100" value="<?php echo $student['marks']; ?>" required readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Attendance Rate</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="attendance" min="0" max="100" value="<?php echo $student['attendance']; ?>" required readonly>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Main Subjects</label>
                                    <div id="subjectsContainer">
                                        <?php if (!empty($student_subjects)): ?>
                                            <?php foreach ($student_subjects as $index => $subject): ?>
                                                <div class="subject-input-group input-group mb-2">
                                                    <input type="text" class="form-control subject-name" placeholder="Subject name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>">
                                                    <input type="color" class="subject-color" value="<?php echo $subject['color']; ?>">
                                                    <button type="button" class="btn btn-danger remove-subject"><i class="fas fa-times"></i></button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="subject-input-group input-group mb-2">
                                                <input type="text" class="form-control subject-name" placeholder="Subject name">
                                                <input type="color" class="subject-color" value="#4e73df">
                                                <button type="button" class="btn btn-danger remove-subject"><i class="fas fa-times"></i></button>
                                            </div>
                                            <div class="subject-input-group input-group mb-2">
                                                <input type="text" class="form-control subject-name" placeholder="Subject name">
                                                <input type="color" class="subject-color" value="#6f42c1">
                                                <button type="button" class="btn btn-danger remove-subject"><i class="fas fa-times"></i></button>
                                            </div>
                                            <div class="subject-input-group input-group mb-2">
                                                <input type="text" class="form-control subject-name" placeholder="Subject name">
                                                <input type="color" class="subject-color" value="#1cc88a">
                                                <button type="button" class="btn btn-danger remove-subject"><i class="fas fa-times"></i></button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addSubject">
                                        <i class="fas fa-plus me-1"></i>Add Subject
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Available Study Hours Per Day</label>
                                    <input type="number" class="form-control" id="studyHours" min="1" max="10" value="4" required>
                                </div>
                                
                                <button type="button" class="btn btn-generate w-100" onclick="generateStudyPlan()">
                                    <i class="fas fa-calendar-plus me-2"></i>Generate Study Plan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-body">
                            <h4 class="card-title">Student Summary</h4>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 id="summaryName"><?php echo $student['student_name']; ?></h5>
                                <span class="badge bg-<?php 
                                    if ($risk_level == 'high') echo 'danger';
                                    elseif ($risk_level == 'medium') echo 'warning';
                                    else echo 'success';
                                ?>" id="summaryRisk"><?php echo $student['risk_level']; ?> Risk</span>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Marks Progress</label>
                                <div class="progress">
                                    <div class="progress-bar" id="marksProgress" role="progressbar" style="width: <?php echo $student['marks']; ?>%;" aria-valuenow="<?php echo $student['marks']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small>0</small>
                                    <small id="marksValue"><?php echo $student['marks']; ?> / 100</small>
                                    <small>100</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Attendance Progress</label>
                                <div class="progress">
                                    <div class="progress-bar bg-success" id="attendanceProgress" role="progressbar" style="width: <?php echo $student['attendance']; ?>%;" aria-valuenow="<?php echo $student['attendance']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small>0%</small>
                                    <small id="attendanceValue"><?php echo $student['attendance']; ?>%</small>
                                    <small>100%</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Selected Subjects</label>
                                <div id="selectedSubjects">
                                    <?php if (!empty($student_subjects)): ?>
                                        <?php foreach ($student_subjects as $subject): ?>
                                            <span class="badge subject-badge" style="background-color: <?php echo $subject['color']; ?>"><?php echo htmlspecialchars($subject['subject_name']); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="badge bg-primary subject-badge">Mathematics</span>
                                        <span class="badge bg-primary subject-badge">Science</span>
                                        <span class="badge bg-primary subject-badge">English</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Study Recommendation</label>
                                <div class="alert alert-<?php 
                                    if ($risk_level == 'high') echo 'danger';
                                    elseif ($risk_level == 'medium') echo 'warning';
                                    else echo 'info';
                                ?>" id="studyRecommendation">
                                    <?php
                                    if ($risk_level == 'high') {
                                        echo 'Based on your high risk level, we recommend an intensive study plan with focus on weak areas.';
                                    } elseif ($risk_level == 'medium') {
                                        echo 'Based on your medium risk level, we recommend a focused study plan with regular reviews.';
                                    } else {
                                        echo 'Based on your low risk level, we recommend a balanced study plan with regular breaks.';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="study-plan animate-fade-in" id="studyPlan" style="display: none;">
                <h4 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Your Personalized Study Plan</h4>
                
                <div class="row" id="planContainer">
                    <!-- Study plan here -->
                </div>
                
                <div class="mt-4">
                    <h5><i class="fas fa-lightbulb me-2"></i>Study Tips</h5>
                    <div class="row" id="studyTips">
                        <!-- Study tips here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('addSubject').addEventListener('click', function() {
            const subjectsContainer = document.getElementById('subjectsContainer');
            const subjectGroup = document.createElement('div');
            subjectGroup.className = 'subject-input-group input-group mb-2';
            
            const randomColor = '#' + Math.floor(Math.random()*16777215).toString(16);
            
            subjectGroup.innerHTML = `
                <input type="text" class="form-control subject-name" placeholder="Subject name">
                <input type="color" class="subject-color" value="${randomColor}">
                <button type="button" class="btn btn-danger remove-subject"><i class="fas fa-times"></i></button>
            `;
            
            subjectsContainer.appendChild(subjectGroup);
            
            subjectGroup.querySelector('.remove-subject').addEventListener('click', function() {
                subjectsContainer.removeChild(subjectGroup);
                updateSummary();
            });

            subjectGroup.querySelector('.subject-name').addEventListener('input', updateSummary);
            subjectGroup.querySelector('.subject-color').addEventListener('input', updateSummary);
        });
        
        document.querySelectorAll('.remove-subject').forEach(button => {
            button.addEventListener('click', function() {
                const subjectGroup = this.closest('.subject-input-group');
                document.getElementById('subjectsContainer').removeChild(subjectGroup);
                updateSummary();
            });
        });

        document.querySelectorAll('.subject-name, .subject-color').forEach(input => {
            input.addEventListener('input', updateSummary);
        });
        
        document.getElementById('riskLevel').addEventListener('change', updateSummary);
        document.getElementById('studyHours').addEventListener('input', updateSummary);
        
        function updateSummary() {
            const name = document.getElementById('studentName').value;
            const riskLevel = document.getElementById('riskLevel').value;
            const marks = document.getElementById('marks').value;
            const attendance = document.getElementById('attendance').value;
            
            document.getElementById('summaryName').textContent = name;

            const riskBadge = document.getElementById('summaryRisk');
            riskBadge.textContent = riskLevel.charAt(0).toUpperCase() + riskLevel.slice(1) + ' Risk';
            
            if (riskLevel === 'high') {
                riskBadge.className = 'badge bg-danger';
            } else if (riskLevel === 'medium') {
                riskBadge.className = 'badge bg-warning';
            } else {
                riskBadge.className = 'badge bg-success';
            }
            

            document.getElementById('marksProgress').style.width = `${marks}%`;
            document.getElementById('marksValue').textContent = `${marks} / 100`;
            
            document.getElementById('attendanceProgress').style.width = `${attendance}%`;
            document.getElementById('attendanceValue').textContent = `${attendance}%`;

            const selectedSubjects = document.getElementById('selectedSubjects');
            selectedSubjects.innerHTML = '';
            
            document.querySelectorAll('.subject-input-group').forEach(group => {
                const subjectName = group.querySelector('.subject-name').value.trim();
                const subjectColor = group.querySelector('.subject-color').value;
                
                if (subjectName) {
                    const badge = document.createElement('span');
                    badge.className = 'badge subject-badge';
                    badge.style.backgroundColor = subjectColor;
                    badge.textContent = subjectName;
                    selectedSubjects.appendChild(badge);
                }
            });
            
            const recommendation = document.getElementById('studyRecommendation');
            if (riskLevel === 'high') {
                recommendation.className = 'alert alert-danger';
                recommendation.textContent = 'Based on your high risk level, we recommend an intensive study plan with focus on weak areas.';
            } else if (riskLevel === 'medium') {
                recommendation.className = 'alert alert-warning';
                recommendation.textContent = 'Based on your medium risk level, we recommend a focused study plan with regular reviews.';
            } else {
                recommendation.className = 'alert alert-info';
                recommendation.textContent = 'Based on your low risk level, we recommend a balanced study plan with regular breaks.';
            }
        }
        
        function generateStudyPlan() {
            const riskLevel = document.getElementById('riskLevel').value;
            const studyHours = parseInt(document.getElementById('studyHours').value);
            
            const subjects = [];
            const subjectColors = {};
            
            document.querySelectorAll('.subject-input-group').forEach(group => {
                const subjectName = group.querySelector('.subject-name').value.trim();
                const subjectColor = group.querySelector('.subject-color').value;
                
                if (subjectName) {
                    subjects.push(subjectName);
                    subjectColors[subjectName] = subjectColor;
                }
            });

            if (subjects.length === 0) {
                subjects.push('Mathematics', 'Science', 'English');
                subjectColors['Mathematics'] = '#4e73df';
                subjectColors['Science'] = '#6f42c1';
                subjectColors['English'] = '#1cc88a';
            }
            
            // Generate study plan based on risk level
            const planContainer = document.getElementById('planContainer');
            planContainer.innerHTML = '';
            
            const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            days.forEach(day => {
                const dayCol = document.createElement('div');
                dayCol.className = 'col-md-6 col-lg-4 mb-3';
                
                const dayCard = document.createElement('div');
                dayCard.className = 'study-day';
                
                const dayTitle = document.createElement('h5');
                dayTitle.className = 'mb-3';
                dayTitle.textContent = day;
                
                dayCard.appendChild(dayTitle);
                
                // Generate study activities based on risk level
                const activities = generateDailyActivities(riskLevel, studyHours, subjects);
                
                activities.forEach(activity => {
                    const activityDiv = document.createElement('div');
                    activityDiv.className = 'mb-2';
                    
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-book me-2';
                    
                    const text = document.createElement('span');
                    text.textContent = activity;
                    
                    activityDiv.appendChild(icon);
                    activityDiv.appendChild(text);
                    dayCard.appendChild(activityDiv);
                });
                
                dayCol.appendChild(dayCard);
                planContainer.appendChild(dayCol);
            });
            
            // Generate study tips based on risk level
            const studyTips = document.getElementById('studyTips');
            studyTips.innerHTML = '';
            
            const tips = generateStudyTips(riskLevel);
            
            tips.forEach(tip => {
                const tipCol = document.createElement('div');
                tipCol.className = 'col-md-6 mb-2';
                
                const tipDiv = document.createElement('div');
                tipDiv.className = 'd-flex';
                
                const icon = document.createElement('i');
                icon.className = 'fas fa-lightbulb text-warning me-2 mt-1';
                
                const text = document.createElement('span');
                text.textContent = tip;
                
                tipDiv.appendChild(icon);
                tipDiv.appendChild(text);
                tipCol.appendChild(tipDiv);
                studyTips.appendChild(tipCol);
            });
            
            document.getElementById('studyPlan').style.display = 'block';
            document.getElementById('studyPlan').scrollIntoView({ behavior: 'smooth' });
        }
        
        function generateDailyActivities(riskLevel, studyHours, subjects) {
            const activities = [];
            
            // Base activities for all risk levels
            activities.push('30min Break');
            
            // Calculate time allocation based on risk level
            let subjectTimeAllocation = [];
            
            if (riskLevel === 'high') {
                // Intensive plan for high risk - more time on each subject
                const baseTime = Math.floor(studyHours * 0.6 / subjects.length);
                const extraTime = studyHours * 0.6 - (baseTime * subjects.length);
                
                for (let i = 0; i < subjects.length; i++) {
                    subjectTimeAllocation.push(baseTime + (i < extraTime ? 1 : 0));
                }
                
                // Add review session
                activities.unshift(`Review weak areas (1h)`);
                
            } else if (riskLevel === 'medium') {
                // Balanced plan for medium risk
                const baseTime = Math.floor(studyHours * 0.7 / subjects.length);
                const extraTime = studyHours * 0.7 - (baseTime * subjects.length);
                
                for (let i = 0; i < subjects.length; i++) {
                    subjectTimeAllocation.push(baseTime + (i < extraTime ? 0.5 : 0));
                }
                
                // Add practice session
                activities.unshift('Practice problems (45min)');
                
            } else {
                // Standard plan for low risk
                const baseTime = Math.floor(studyHours * 0.8 / subjects.length);
                const extraTime = studyHours * 0.8 - (baseTime * subjects.length);
                
                for (let i = 0; i < subjects.length; i++) {
                    subjectTimeAllocation.push(baseTime + (i < extraTime ? 0.5 : 0));
                }
            }
            
            // Add subjects to activities
            for (let i = 0; i < subjects.length; i++) {
                if (subjectTimeAllocation[i] > 0) {
                    activities.unshift(`${subjects[i]} (${subjectTimeAllocation[i]}h)`);
                }
            }
            
            return activities;
        }
        
        function generateStudyTips(riskLevel) {
            if (riskLevel === 'high') {
                return [
                    'Focus on your weakest subjects first',
                    'Study in 45-minute blocks with 15-minute breaks',
                    'Create flashcards for key concepts',
                    'Seek help from tutors or study groups',
                    'Practice with past exam papers'
                ];
            } else if (riskLevel === 'medium') {
                return [
                    'Balance your study time across all subjects',
                    'Review material regularly to reinforce learning',
                    'Use mnemonic devices to remember key information',
                    'Teach concepts to someone else to test your understanding',
                    'Stay consistent with your study schedule'
                ];
            } else {
                return [
                    'Maintain a consistent study routine',
                    'Focus on understanding rather than memorization',
                    'Take regular breaks to avoid burnout',
                    'Set specific goals for each study session',
                    'Explore topics beyond the curriculum for deeper understanding'
                ];
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            updateSummary();
        });
    </script>
</body>
</html>
<?php
// api/journal_data.php
session_start();
require_once __DIR__ . '/../auth.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->checkAuth()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = $auth->getCurrentUser();
$db = Database::getInstance()->getConnection();

$subjectId = $_GET['subject_id'] ?? null;
$classId = $_GET['class_id'] ?? null;

// Если учитель — определяем его классы и предметы из teacher_assignments
if ($user['role'] === 'teacher') {
    // Получаем первое назначение учителя по умолчанию
    $stmt = $db->prepare("SELECT class_id, subject_id FROM teacher_assignments WHERE teacher_id = ? LIMIT 1");
    $stmt->execute([$user['id']]);
    $assignment = $stmt->fetch();
    
    if ($assignment) {
        if (!$classId) $classId = $assignment['class_id'];
        if (!$subjectId) $subjectId = $assignment['subject_id'];
    }
}

try {
    // Получаем предметы
    if ($user['role'] === 'teacher') {
        // Учитель видит только свои предметы в выбранном классе
        $stmt = $db->prepare("
            SELECT s.* FROM subjects s
            INNER JOIN teacher_assignments ta ON s.id = ta.subject_id AND s.class_id = ta.class_id
            WHERE s.class_id = ? AND ta.teacher_id = ?
        ");
        $stmt->execute([$classId, $user['id']]);
    } else {
        $stmt = $db->prepare("SELECT * FROM subjects WHERE class_id = ?");
        $stmt->execute([$classId]);
    }
    $subjects = $stmt->fetchAll();

    // Получаем уроки
    $stmt = $db->prepare("
        SELECT id, subject_id, lesson_number, date, topic, homework, individual_task, quarter, position
        FROM lessons
        WHERE subject_id = ?
        ORDER BY position, lesson_number
    ");
    $stmt->execute([$subjectId]);
    $lessons = $stmt->fetchAll();

    // Получаем учащихся
    if ($user['role'] === 'parent') {
        $stmt = $db->prepare("
            SELECT s.id, s.full_name, s.class_id, s.position, s.user_id
            FROM students s
            JOIN parent_student ps ON s.id = ps.student_id
            WHERE ps.parent_id = ?
            ORDER BY s.position
        ");
        $stmt->execute([$user['id']]);
    } elseif ($user['role'] === 'student') {
        $stmt = $db->prepare("
            SELECT id, full_name, class_id, position, user_id
            FROM students
            WHERE user_id = ?
            ORDER BY position
        ");
        $stmt->execute([$user['id']]);
    } else {
        $stmt = $db->prepare("
            SELECT id, full_name, class_id, position, user_id
            FROM students
            WHERE class_id = ?
            ORDER BY position
        ");
        $stmt->execute([$classId]);
    }
    $students = $stmt->fetchAll();

    // ✅ Получаем оценки с комментариями
    $grades = [];
    $averages = [];

    foreach ($students as $student) {
        $stmt = $db->prepare("
            SELECT lesson_id, grade, comment
            FROM grades
            WHERE student_id = ?
        ");
        $stmt->execute([$student['id']]);
        $studentGrades = $stmt->fetchAll();

        foreach ($studentGrades as $grade) {
            // ✅ Сохраняем и оценку, и комментарий
            $grades[$student['id']][$grade['lesson_id']] = [
                'grade' => $grade['grade'],
                'comment' => $grade['comment'] ?? ''
            ];
        }

        // Считаем средний балл ТОЛЬКО по текущему предмету
        $stmt = $db->prepare("
            SELECT AVG(g.grade) as avg_grade
            FROM grades g
            INNER JOIN lessons l ON g.lesson_id = l.id
            WHERE g.student_id = ? AND l.subject_id = ?
        ");
        $stmt->execute([$student['id'], $subjectId]);
        $avgResult = $stmt->fetch();
        $averages[$student['id']] = $avgResult['avg_grade'] ? round($avgResult['avg_grade'], 2) : '-';
    }

    echo json_encode([
        'success' => true,
        'subjects' => $subjects,
        'lessons' => $lessons,
        'students' => $students,
        'grades' => $grades,  // ✅ Теперь с комментариями
        'averages' => $averages,
        'user_role' => $user['role'],
        'user_id' => $user['id']
    ]);

} catch (PDOException $e) {
    error_log("Journal data error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
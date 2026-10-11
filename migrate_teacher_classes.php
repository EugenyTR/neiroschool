<?php
// migrate_teacher_classes.php - Создаёт таблицу для связи учителей с несколькими классами/предметами
require_once 'config.php';
$db = Database::getInstance()->getConnection();

try {
    // Создаём таблицу teacher_assignments (назначения учителей)
    $db->exec("
        CREATE TABLE IF NOT EXISTS teacher_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            teacher_id INT NOT NULL,
            class_id INT NOT NULL,
            subject_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_assignment (teacher_id, class_id, subject_id),
            FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Переносим существующие привязки из users
    $db->exec("
        INSERT IGNORE INTO teacher_assignments (teacher_id, class_id, subject_id)
        SELECT id, class_id, subject_id FROM users 
        WHERE role = 'teacher' AND class_id IS NOT NULL AND subject_id IS NOT NULL
    ");

    echo "✅ Миграция завершена!\n";
    echo "- Создана таблица teacher_assignments\n";
    echo "- Существующие привязки учителей перенесены\n";
    echo "- Теперь учитель может вести несколько предметов в разных классах\n";

} catch (PDOException $e) {
    echo "❌ Ошибка миграции: " . $e->getMessage() . "\n";
}

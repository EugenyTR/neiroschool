<?php
// migrate_add_methodologist.php - Добавляет роль методиста и привязку учителей к предметам
require_once 'config.php';
$db = Database::getInstance()->getConnection();

try {
    // 1. Добавляем колонку subject_id в таблицу users (для учителей)
    $db->exec("ALTER TABLE users ADD COLUMN subject_id INT NULL AFTER role");
    
    // 2. Добавляем колонку class_id в таблицу users (для учителей и методистов)
    $db->exec("ALTER TABLE users ADD COLUMN class_id INT NULL AFTER subject_id");
    
    // 3. Создаём индекс для быстрого поиска
    $db->exec("ALTER TABLE users ADD INDEX idx_subject (subject_id)");
    $db->exec("ALTER TABLE users ADD INDEX idx_class (class_id)");
    
    // 4. Обновляем существующих учителей - привязываем к первому предмету
    $db->exec("UPDATE users SET subject_id = 1, class_id = 1 WHERE role = 'teacher' AND subject_id IS NULL");
    
    echo "✅ Миграция завершена успешно!\n";
    echo "- Добавлена роль 'methodologist' (можно использовать в users.role)\n";
    echo "- Добавлены поля subject_id и class_id в таблицу users\n";
    echo "- Существующие учителя привязаны к предмету ID=1 и классу ID=1\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка миграции: " . $e->getMessage() . "\n";
}

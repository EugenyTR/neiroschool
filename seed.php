<?php
require_once 'config.php';
$db=Database::getInstance()->getConnection();

// Сначала создаём классы и предметы, чтобы получить их ID
$db->exec("INSERT INTO classes(name,teacher_id)VALUES('1А',2) ON DUPLICATE KEY UPDATE name=name");

// Создаём предметы, если их нет
$db->exec("INSERT IGNORE INTO subjects(name,teacher_id,class_id)VALUES('Русский язык',2,1),('Математика',2,1),('Окружающий мир',2,1)");

$users=[
    ['admin','admin123','principal','Директор Иванов',null,null],
    ['methodologist1','method123','methodologist','Методист Петрова',null,1],
    ['teacher1','teacher123','teacher','Иванова А.А.',1,1],  // Русский язык
    ['teacher2','teacher123','teacher','Сидоров Б.Б.',2,1],  // Математика
    ['parent1','parent123','parent','Петров П.П.',null,null],
    ['student1','student123','student','Иванов Артём',null,null]
];

foreach($users as $u){
    // Проверяем, существует ли уже пользователь
    $stmt=$db->prepare("SELECT id FROM users WHERE username=?");
    $stmt->execute([$u[0]]);
    if($stmt->fetch()) continue;
    
    $stmt=$db->prepare("INSERT INTO users(username,password_hash,role,full_name,subject_id,class_id)VALUES(?,?,?,?,?,?)");
    $stmt->execute([$u[0],password_hash($u[1],PASSWORD_DEFAULT),$u[2],$u[3],$u[4],$u[5]]);
}

// Создаём teacher_assignments для демонстрации множественных назначений
// teacher1 (id=3) — Русский язык в 1А
// teacher2 (id=4) — Математика в 1А + Математика в 2А (если есть)
$db->exec("
    INSERT IGNORE INTO teacher_assignments (teacher_id, class_id, subject_id)
    SELECT u.id, u.class_id, u.subject_id FROM users u
    WHERE u.role = 'teacher' AND u.class_id IS NOT NULL AND u.subject_id IS NOT NULL
");
$db->exec("INSERT INTO classes(name,teacher_id)VALUES('1А',2)");
$db->exec("INSERT INTO subjects(name,teacher_id,class_id)VALUES('Русский язык',2,1),('Математика',2,1),('Окружающий мир',2,1)");
$students=['Иванов Артём','Петров Эрик','Давыдова Кира','Сидоров Сергей','Тригубович Полина','Соловьева София','Карамзин Ричард','Вернадская София','Лаврентьева Виктория','Королева Дарья','Джугашвили Матвей'];
foreach($students as $i=>$name){
    $stmt=$db->prepare("INSERT INTO students(full_name,class_id,position)VALUES(?,1,?)");
    $stmt->execute([$name,$i]);
}
for($i=1;$i<=15;$i++){
    $d=date('Y-m-d',strtotime("2023-09-01 +".($i-1)." days"));
    $stmt=$db->prepare("INSERT INTO lessons(subject_id,lesson_number,date,topic,position)VALUES(1,?,?,?,?)");
    $stmt->execute([1,$i,$d,'Урок '.$i,100]);
}
echo"✅ Seed completed. Logins: admin/admin123, teacher1/teacher123, parent1/parent123, student1/student123";
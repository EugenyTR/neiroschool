<?php
// index.php
session_start();
require_once __DIR__.'/auth.php';

$auth = new Auth();
if (!$auth->checkAuth()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>ЯШК | Электронный журнал 1А</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<?php
$roleLabels = [
    'principal' => 'Директор',
    'methodologist' => 'Методист',
    'teacher' => 'Педагог',
    'parent' => 'Родитель',
    'student' => 'Ученик'
];
$currentRoleLabel = $roleLabels[$user['role']] ?? $user['role'];
?>
<body data-username="<?=htmlspecialchars($user['username'])?>" data-role-label="<?=htmlspecialchars($currentRoleLabel)?>" data-full-name="<?=htmlspecialchars($user['full_name'])?>" data-email="<?=htmlspecialchars($user['email'] ?? '')?>">

<header class="header">
    <div class="header-content">
        <img src="./images/logo.svg" alt="ЯШК" />
        <h1>Электронный журнал</h1>
        <div class="header-info">
            <select id="classSelect" class="subject-select">
                <option value="">Загрузка классов...</option>
            </select>
            <select id="subjectSelect" class="subject-select">
                <option value="">Загрузка предметов...</option>
            </select>
            <?php if($auth->hasRole(['principal','methodologist'])): ?>
            <button id="addSubjectBtn" class="btn btn-primary" style="padding:6px 12px;font-size:14px;" title="Добавить предмет">+ Предмет</button>
            <?php endif; ?>
        </div>
        <div class="user-info">
            <span class="user-name"><?=htmlspecialchars($user['full_name'])?></span>
            <span class="role-badge"><?=htmlspecialchars($currentRoleLabel)?></span>
            <button id="profileBtn" class="btn" title="Личный кабинет">👤 Профиль</button>
            <a href="logout.php" class="btn">Выход</a>
        </div>
    </div>
</header>

<div class="teacher-bar">
    <span class="teacher-name"><?=htmlspecialchars($currentRoleLabel)?>: <strong><?=htmlspecialchars($user['full_name'])?></strong></span>
    <span id="currentClassLabel" class="quarter-info">Загрузка...</span>
</div>

<main class="journal-container">
    <div class="controls">
        <?php if($auth->hasRole('principal')): ?>
        <button id="addStudentBtn" class="btn btn-primary">
            <span>+</span> Ученик
        </button>
        <?php endif; ?>

        <?php if($auth->hasRole(['principal','teacher'])): ?>
        <button id="addLessonBtn" class="btn btn-primary">
            <span>+</span> Урок
        </button>
        <button id="exportBtn" class="btn">
            <span>📥</span> Экспорт
        </button>
        <?php endif; ?>

        <?php if($auth->hasRole(['principal','methodologist'])): ?>
        <button id="manageClassesBtn" class="btn btn-secondary">
            <span>🏫</span> Классы
        </button>
        <button id="manageUsersBtn" class="btn btn-secondary">
            <span>👥</span> Пользователи
        </button>
        <?php endif; ?>
    </div>

    <!-- ТАБЛИЦА 1: Оценки с датами над колонками -->
    <div class="table-section">
        <h3 class="table-title">📝 Оценки учащихся</h3>
        <div class="table-wrapper">
            <table class="journal-table" id="gradesTable">
                <thead>
                    <!-- Строка 1: Даты уроков (редактируемые) — теперь каждая дата в отдельном <th> -->
                    <tr class="lesson-dates-header" id="gradesDateHeaderRow">
                        <th class="col-drag">↕</th>
                        <th class="col-number"></th>
                        <th class="col-student"></th>
                        <!-- Ячейки для дат будут добавлены динамически через JS -->
                        <th class="col-average"></th>
                    </tr>
                    <!-- Строка 2: Номера уроков — каждая колонка с номером -->
                    <tr class="header-sticky" id="gradesNumberHeaderRow">
                        <th class="col-drag"></th>
                        <th class="col-number">№</th>
                        <th class="col-student">Ф.И. учащегося</th>
                        <!-- Ячейки для номеров будут добавлены динамически через JS -->
                        <th class="col-average">Средний балл</th>
                    </tr>
                </thead>
                <tbody id="gradesBody">
                    <tr><td colspan="100" style="padding:40px;text-align:center;color:#999;">Загрузка данных...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ТАБЛИЦА 2: Информация об уроках -->
    <div class="table-section">
        <h3 class="table-title">📅 Информация об уроках</h3>
        <div class="table-wrapper">
            <table class="journal-table" id="lessonsTable">
                <thead>
                    <tr class="header-sticky">
                        <th class="col-number">№</th>
                        <th class="col-date">Дата</th>
                        <th class="col-topic">Тема урока</th>
                        <th class="col-task">Индив. задание</th>
                        <th class="col-actions">Действия</th>
                    </tr>
                </thead>
                <tbody id="lessonsBody">
                    <tr><td colspan="5" style="padding:40px;text-align:center;color:#999;">Загрузка данных...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal для оценки -->
<div id="gradeModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Ввод оценки</h3>
        <form id="gradeForm">
            <input type="hidden" id="gradeStudentId">
            <input type="hidden" id="gradeLessonId">
            <div class="form-group">
                <label>Баллы (0-100) *</label>
                <input type="number" id="gradeValue" min="0" max="100" step="1" required placeholder="0-100">
            </div>
            <div class="form-group">
                <label>Комментарий</label>
                <textarea id="gradeComment" rows="3" placeholder="Необязательно"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
        </form>
    </div>
</div>

<!-- Modal для урока -->
<div id="lessonModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Добавить урок</h3>
        <form id="lessonForm">
            <div class="form-group">
                <label>Дата урока *</label>
                <input type="date" id="lessonDate" required>
            </div>
            <div class="form-group">
                <label>Тема урока *</label>
                <textarea id="lessonTopic" rows="3" required placeholder="Введите тему урока"></textarea>
            </div>
            <div class="form-group">
                <label>Индивидуальное задание</label>
                <textarea id="lessonTask" rows="2" placeholder="Задание для ученика"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Добавить урок</button>
        </form>
    </div>
</div>

<!-- Modal для редактирования урока -->
<div id="editLessonModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Редактировать урок</h3>
        <form id="editLessonForm">
            <input type="hidden" id="editLessonId">
            <div class="form-group">
                <label>Дата урока *</label>
                <input type="date" id="editLessonDate" required>
            </div>
            <div class="form-group">
                <label>Тема урока *</label>
                <textarea id="editLessonTopic" rows="3" required placeholder="Введите тему урока"></textarea>
            </div>
            <div class="form-group">
                <label>Индивидуальное задание</label>
                <textarea id="editLessonTask" rows="2" placeholder="Задание для ученика"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
        </form>
    </div>
</div>

<!-- Modal для редактирования даты в таблице оценок -->
<div id="editDateModal" class="modal">
    <div class="modal-content" style="max-width:400px;">
        <span class="close">&times;</span>
        <h3>Изменить дату урока</h3>
        <form id="editDateForm">
            <input type="hidden" id="editDateLessonId">
            <div class="form-group">
                <label>Новая дата *</label>
                <input type="date" id="editDateValue" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
        </form>
    </div>
</div>

<?php if($auth->hasRole(['principal','methodologist'])): ?>
<!-- ПАНЕЛЬ УПРАВЛЕНИЯ ПРЕДМЕТАМИ -->
<div id="subjectsPanel" class="table-section" style="display:none;">
    <h3 class="table-title">📚 Управление предметами</h3>
    <div class="controls" style="margin-bottom:15px;">
        <button id="addSubjectPanelBtn" class="btn btn-primary"><span>+</span> Добавить предмет</button>
    </div>
    <div class="table-wrapper">
        <table class="journal-table" id="subjectsTable">
            <thead>
                <tr class="header-sticky">
                    <th class="col-number">№</th>
                    <th>Название предмета</th>
                    <th>Класс</th>
                    <th class="col-actions">Действия</th>
                </tr>
            </thead>
            <tbody id="subjectsBody">
                <tr><td colspan="4" style="padding:40px;text-align:center;color:#999;">Загрузка...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ПАНЕЛЬ УПРАВЛЕНИЯ КЛАССАМИ -->
<div id="classesPanel" class="table-section" style="display:none;">
    <h3 class="table-title">🏫 Управление классами</h3>
    <div class="controls" style="margin-bottom:15px;">
        <button id="addClassBtn" class="btn btn-primary"><span>+</span> Добавить класс</button>
    </div>
    <div class="table-wrapper">
        <table class="journal-table" id="classesTable">
            <thead>
                <tr class="header-sticky">
                    <th class="col-number">№</th>
                    <th>Название класса</th>
                    <th class="col-actions">Действия</th>
                </tr>
            </thead>
            <tbody id="classesBody">
                <tr><td colspan="3" style="padding:40px;text-align:center;color:#999;">Загрузка...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ПАНЕЛЬ УПРАВЛЕНИЯ ПОЛЬЗОВАТЕЛЯМИ -->
<div id="usersPanel" class="table-section" style="display:none;">
    <h3 class="table-title">👥 Управление пользователями</h3>
    <div class="controls" style="margin-bottom:15px;">
        <button id="addUserBtn" class="btn btn-primary"><span>+</span> Добавить пользователя</button>
    </div>
    <div class="table-wrapper">
        <table class="journal-table" id="usersTable">
            <thead>
                <tr class="header-sticky">
                    <th class="col-number">№</th>
                    <th>Ф.И.О.</th>
                    <th>Логин</th>
                    <th>Роль</th>
                    <th colspan="2">Назначения (класс: предмет)</th>
                    <th class="col-actions">Действия</th>
                </tr>
            </thead>
            <tbody id="usersBody">
                <tr><td colspan="7" style="padding:40px;text-align:center;color:#999;">Загрузка...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Modal для добавления предмета -->
<div id="subjectModal" class="modal">
    <div class="modal-content" style="max-width:400px;">
        <span class="close">&times;</span>
        <h3>Добавить предмет</h3>
        <form id="subjectForm">
            <div class="form-group">
                <label>Класс *</label>
                <select id="subjectClassId" required>
                    <option value="">Выберите класс</option>
                </select>
            </div>
            <div class="form-group">
                <label>Название предмета *</label>
                <input type="text" id="subjectName" required placeholder="Например: Русский язык, Математика">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Создать</button>
        </form>
    </div>
</div>

<!-- Modal для добавления класса -->
<div id="classModal" class="modal">
    <div class="modal-content" style="max-width:400px;">
        <span class="close">&times;</span>
        <h3>Добавить класс</h3>
        <form id="classForm">
            <div class="form-group">
                <label>Название класса *</label>
                <input type="text" id="className" required placeholder="Например: 1А, 2Б, 10А">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Создать</button>
        </form>
    </div>
</div>

<!-- Modal для личного кабинета -->
<div id="profileModal" class="modal">
    <div class="modal-content" style="max-width:450px;">
        <span class="close">&times;</span>
        <h3>👤 Личный кабинет</h3>
        <form id="profileForm">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" id="profileUsername" disabled style="background:#f5f5f5;">
            </div>
            <div class="form-group">
                <label>Роль</label>
                <input type="text" id="profileRole" disabled style="background:#f5f5f5;">
            </div>
            <div class="form-group">
                <label>Ф.И.О. *</label>
                <input type="text" id="profileFullName" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="profileEmail">
            </div>
            <hr style="margin:20px 0;border:0;border-top:1px solid #eee;">
            <h4 style="margin-bottom:15px;">Смена пароля</h4>
            <div class="form-group">
                <label>Текущий пароль <small>(обязателен для смены пароля)</small></label>
                <input type="password" id="profileCurrentPassword" placeholder="Введите текущий пароль">
            </div>
            <div class="form-group">
                <label>Новый пароль <small>(оставьте пустым, чтобы не менять)</small></label>
                <input type="password" id="profileNewPassword" placeholder="минимум 6 символов">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
        </form>
    </div>
</div>

<!-- Modal для добавления пользователя -->
<div id="userModal" class="modal">
    <div class="modal-content" style="max-width:500px;">
        <span class="close">&times;</span>
        <h3>Добавить пользователя</h3>
        <form id="userForm">
            <div class="form-group">
                <label>Роль *</label>
                <select id="userRole" required>
                    <option value="">Выберите роль</option>
                    <option value="teacher">Педагог</option>
                    <option value="methodologist">Методист</option>
                </select>
            </div>
            <div class="form-group">
                <label>Ф.И.О. *</label>
                <input type="text" id="userFullName" required placeholder="Иванова А.А.">
            </div>
            <div class="form-group">
                <label>Логин *</label>
                <input type="text" id="userUsername" required placeholder="teacher1">
            </div>
            <div class="form-group">
                <label>Пароль *</label>
                <input type="text" id="userPassword" required placeholder="минимум 6 символов">
            </div>
            <div id="userAssignmentsBlock" style="display:none;">
                <label>Назначения (класс + предмет)</label>
                <div id="userAssignmentsList"></div>
                <button type="button" class="btn" style="margin-top:8px;font-size:13px;" onclick="addAssignmentRow('userAssignmentsList')">+ Добавить назначение</button>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="userEmail" placeholder="optional@school.ru">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Создать</button>
        </form>
    </div>
</div>

<!-- Modal для редактирования пользователя -->
<div id="editUserModal" class="modal">
    <div class="modal-content" style="max-width:500px;">
        <span class="close">&times;</span>
        <h3>Редактировать пользователя</h3>
        <form id="editUserForm">
            <input type="hidden" id="editUserId">
            <div class="form-group">
                <label>Ф.И.О. *</label>
                <input type="text" id="editUserFullName" required>
            </div>
            <div id="editAssignmentsBlock">
                <label>Назначения (класс + предмет)</label>
                <div id="editAssignmentsList"></div>
                <button type="button" class="btn" style="margin-top:8px;font-size:13px;" onclick="addAssignmentRow('editAssignmentsList')">+ Добавить назначение</button>
            </div>
            <div class="form-group">
                <label>Новый пароль <small>(оставьте пустым, чтобы не менять)</small></label>
                <input type="text" id="editUserPassword" placeholder="не менять">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Сохранить</button>
        </form>
    </div>
</div>

<script src="js/app.js"></script>
</body>
</html>
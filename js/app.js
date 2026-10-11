// js/app.js — ПЕРВАЯ СТРОКА, без BOM!
document.addEventListener('DOMContentLoaded', function(){
    // === ЭЛЕМЕНТЫ DOM ===
    const classSelect = document.getElementById('classSelect');
    const subjectSelect = document.getElementById('subjectSelect');
    const currentClassLabel = document.getElementById('currentClassLabel');
    const gradesBody = document.getElementById('gradesBody');
    const lessonsBody = document.getElementById('lessonsBody');
    const gradesNumberHeaderRow = document.getElementById('gradesNumberHeaderRow');
    const gradesDateHeaderRow = document.getElementById('gradesDateHeaderRow');
    const gradeModal = document.getElementById('gradeModal');
    const lessonModal = document.getElementById('lessonModal');
    const editLessonModal = document.getElementById('editLessonModal');
    const editDateModal = document.getElementById('editDateModal');
    const subjectModal = document.getElementById('subjectModal');
    const profileModal = document.getElementById('profileModal');
    const classModal = document.getElementById('classModal');
    const userModal = document.getElementById('userModal');
    const editUserModal = document.getElementById('editUserModal');
    const closeBtns = document.querySelectorAll('.close');
    const gradeForm = document.getElementById('gradeForm');
    const lessonForm = document.getElementById('lessonForm');
    const editLessonForm = document.getElementById('editLessonForm');
    const editDateForm = document.getElementById('editDateForm');
    const subjectForm = document.getElementById('subjectForm');
    const classForm = document.getElementById('classForm');
    const userForm = document.getElementById('userForm');
    const editUserForm = document.getElementById('editUserForm');
    const profileForm = document.getElementById('profileForm');
    const profileBtn = document.getElementById('profileBtn');
    const addStudentBtn = document.getElementById('addStudentBtn');
    const addLessonBtn = document.getElementById('addLessonBtn');
    const addSubjectBtn = document.getElementById('addSubjectBtn');
    const manageClassesBtn = document.getElementById('manageClassesBtn');
    const manageUsersBtn = document.getElementById('manageUsersBtn');
    const addSubjectPanelBtn = document.getElementById('addSubjectPanelBtn');
    const addClassBtn = document.getElementById('addClassBtn');
    const addUserBtn = document.getElementById('addUserBtn');
    const subjectsPanel = document.getElementById('subjectsPanel');
    const subjectsBody = document.getElementById('subjectsBody');
    const classesPanel = document.getElementById('classesPanel');
    const classesBody = document.getElementById('classesBody');
    const usersPanel = document.getElementById('usersPanel');
    const usersBody = document.getElementById('usersBody');

    let userRole = '';
    let currentClassId = 1;
    let currentSubject = 1;
    let lessonsData = [];

    // === ОБРАБОТЧИКИ СОБЫТИЙ ===

    // Закрытие модалок
    closeBtns.forEach(btn => {
        btn.onclick = function(){
            gradeModal.style.display = 'none';
            lessonModal.style.display = 'none';
            editLessonModal.style.display = 'none';
            editDateModal.style.display = 'none';
            if(profileModal) profileModal.style.display = 'none';
            if(subjectModal) subjectModal.style.display = 'none';
            if(classModal) classModal.style.display = 'none';
            if(userModal) userModal.style.display = 'none';
            if(editUserModal) editUserModal.style.display = 'none';
        };
    });

    // Клик вне модалки закрывает её
    window.onclick = function(e){
        if(e.target === gradeModal) gradeModal.style.display = 'none';
        if(e.target === lessonModal) lessonModal.style.display = 'none';
        if(e.target === editLessonModal) editLessonModal.style.display = 'none';
        if(e.target === editDateModal) editDateModal.style.display = 'none';
        if(e.target === profileModal) profileModal.style.display = 'none';
        if(e.target === subjectModal) subjectModal.style.display = 'none';
        if(e.target === classModal) classModal.style.display = 'none';
        if(e.target === userModal) userModal.style.display = 'none';
        if(e.target === editUserModal) editUserModal.style.display = 'none';
    };

    // Смена класса
    if(classSelect) {
        classSelect.addEventListener('change', function(){
            currentClassId = this.value;
            currentSubject = '';
            subjectSelect.innerHTML = '<option value="">Загрузка предметов...</option>';
            if(currentClassId) {
                loadSubjectsForClass(currentClassId);
            } else {
                subjectSelect.innerHTML = '<option value="">Сначала выберите класс</option>';
                gradesBody.innerHTML = '<tr><td colspan="100" style="padding:40px;text-align:center;color:#666;">Выберите класс и предмет</td></tr>';
                lessonsBody.innerHTML = '<tr><td colspan="5" style="padding:40px;text-align:center;color:#666;">Выберите класс и предмет</td></tr>';
            }
        });
    }

    // Смена предмета
    if(subjectSelect) {
        subjectSelect.addEventListener('change', function(){
            currentSubject = this.value;
            loadJournalData();
        });
    }

    // Формы
    if(gradeForm) gradeForm.onsubmit = function(e){ e.preventDefault(); saveGrade(); };
    if(lessonForm) lessonForm.onsubmit = function(e){ e.preventDefault(); addLesson(); };
    if(editLessonForm) editLessonForm.onsubmit = function(e){ e.preventDefault(); saveEditLesson(); };
    if(editDateForm) editDateForm.onsubmit = function(e){ e.preventDefault(); saveEditDate(); };
    if(subjectForm) subjectForm.onsubmit = function(e){ e.preventDefault(); addSubject(); };
    if(classForm) classForm.onsubmit = function(e){ e.preventDefault(); addClass(); };
    if(userForm) userForm.onsubmit = function(e){ e.preventDefault(); addUser(); };
    if(editUserForm) editUserForm.onsubmit = function(e){ e.preventDefault(); saveEditUser(); };
    if(profileForm) profileForm.onsubmit = function(e){ e.preventDefault(); saveProfile(); };

    // Кнопки
    if(addStudentBtn) addStudentBtn.onclick = addStudent;
    if(addLessonBtn) addLessonBtn.onclick = function(){ lessonModal.style.display = 'block'; };
    if(addSubjectBtn) addSubjectBtn.onclick = function(){ openSubjectModal(currentClassId); };
    if(manageClassesBtn) manageClassesBtn.onclick = toggleClassesPanel;
    if(manageUsersBtn) manageUsersBtn.onclick = toggleUsersPanel;
    if(addSubjectPanelBtn) addSubjectPanelBtn.onclick = function(){ openSubjectModal(); };
    if(addClassBtn) addClassBtn.onclick = function(){ classModal.style.display = 'block'; };
    if(addUserBtn) addUserBtn.onclick = function(){ openUserModal(); };
    if(profileBtn) profileBtn.onclick = openProfileModal;

    // Глобальные функции для onclick-атрибутов
    window.openGradeModal = openGradeModal;
    window.openEditLessonModal = openEditLessonModal;
    window.openEditDateModal = openEditDateModal;
    window.openAddLessonModal = function() { lessonModal.style.display = 'block'; };
    window.deleteLesson = deleteLesson;
    window.deleteSubject = deleteSubject;
    window.deleteClass = deleteClass;
    window.editUser = editUser;
    window.deleteUser = deleteUser;
    window.editStudent = editStudent;
    window.deleteStudent = deleteStudent;
    window.addAssignmentRow = addAssignmentRow;

    // Загрузка данных при старте
    loadClasses();

    // === ЗАГРУЗКА КЛАССОВ ===
    function loadClasses(){
        if(!classSelect) return;

        fetch('api/get_classes.php')
            .then(r => r.json())
            .then(data => {
                if(!data.success) return;

                // Сохраняем роль пользователя
                if(data.user_role) userRole = data.user_role;

                // Если учитель — фиксируем его класс
                if(data.user_role === 'teacher' && data.user_class_id) {
                    currentClassId = data.user_class_id;
                    classSelect.disabled = true;
                    classSelect.title = 'Доступен только ваш класс';
                } else {
                    classSelect.disabled = false;
                    classSelect.title = '';
                }

                let options = '<option value="">Выберите класс</option>';
                data.classes.forEach(c => {
                    const selected = c.id == currentClassId ? ' selected' : '';
                    options += '<option value="' + c.id + '"' + selected + '>' + escapeHtml(c.name) + '</option>';
                });
                classSelect.innerHTML = options;

                // Обновляем label
                if(currentClassLabel && classSelect.selectedIndex > 0) {
                    currentClassLabel.textContent = classSelect.options[classSelect.selectedIndex].text + ' класс';
                }

                // Если класс выбран — загружаем предметы
                if(currentClassId) {
                    loadSubjectsForClass(currentClassId);
                }
            })
            .catch(err => console.error('Load classes error:', err));
    }

    function loadSubjectsForClass(classId){
        if(!subjectSelect || !classId) return;

        fetch('api/get_subjects.php?class_id=' + classId)
            .then(r => r.json())
            .then(data => {
                if(!data.success) return;

                // Если учитель — фиксируем его предмет
                if(userRole === 'teacher' && data.subjects.length === 1) {
                    currentSubject = data.subjects[0].id;
                    subjectSelect.disabled = true;
                    subjectSelect.title = 'Доступен только ваш предмет';
                } else {
                    subjectSelect.disabled = false;
                    subjectSelect.title = '';
                }

                let options = '<option value="">Выберите предмет</option>';
                if(data.subjects && data.subjects.length > 0) {
                    data.subjects.forEach(s => {
                        const selected = s.id == currentSubject ? ' selected' : '';
                        options += '<option value="' + s.id + '"' + selected + '>' + escapeHtml(s.name) + '</option>';
                    });
                }
                subjectSelect.innerHTML = options;

                // Если предмет уже выбран — загружаем данные
                if(currentSubject) {
                    loadJournalData();
                }
            })
            .catch(err => console.error('Load subjects error:', err));
    }

    // === ЗАГРУЗКА ДАННЫХ ЖУРНАЛА ===
    function loadJournalData(){
        if(!currentSubject) {
            gradesBody.innerHTML = '<tr><td colspan="100" style="padding:40px;text-align:center;color:#666;">Выберите предмет</td></tr>';
            lessonsBody.innerHTML = '<tr><td colspan="5" style="padding:40px;text-align:center;color:#666;">Выберите предмет</td></tr>';
            return;
        }

        gradesBody.innerHTML = '<tr><td colspan="100" style="padding:40px;text-align:center;color:#666;">Загрузка...</td></tr>';
        lessonsBody.innerHTML = '<tr><td colspan="5" style="padding:40px;text-align:center;color:#666;">Загрузка...</td></tr>';

        fetch('api/journal_data.php?subject_id=' + currentSubject + '&class_id=' + currentClassId)
            .then(r => r.json())
            .then(data => {
                if(!data.success) {
                    throw new Error(data.error || 'Ошибка загрузки');
                }

                userRole = data.user_role;
                lessonsData = data.lessons;

                // Обновляем label текущего класса
                if(currentClassLabel && classSelect) {
                    const selectedOption = classSelect.options[classSelect.selectedIndex];
                    currentClassLabel.textContent = selectedOption && selectedOption.value ? selectedOption.text + ' класс' : 'Класс не выбран';
                }

                const editable = (userRole === 'principal' || userRole === 'methodologist' || userRole === 'teacher');
                const canManageStudents = (userRole === 'principal');

                // === ТАБЛИЦА 1: ОЦЕНКИ С ДАТАМИ НАД КАЖДОЙ КОЛОНКОЙ ===

                // Рендер заголовков с датами (СТРОКА 1 - ДАТЫ)
                let datesHtml = '<th class="col-drag">↕</th><th class="col-number"></th><th class="col-student"></th>';
                data.lessons.forEach(l => {
                    const dateClass = editable ? 'lesson-date-header editable-date' : 'lesson-date-header';
                    const dateOnclick = editable ? 'openEditDateModal(' + l.id + ',\'' + (l.date || '') + '\')' : '';
                    const deleteBtn = editable ? '<button class="btn-delete-col" onclick="event.stopPropagation(); deleteLesson(' + l.id + ')" title="Удалить урок">×</button>' : '';
                    datesHtml += '<th class="' + dateClass + '" title="Нажмите для редактирования даты">' +
                        '<span onclick="' + dateOnclick + '">' + (l.date ? formatDate(l.date) : '-') + '</span>' +
                        deleteBtn + '</th>';
                });
                if(editable) {
                    datesHtml += '<th class="lesson-date-header col-add-lesson"><button class="btn-add-col" onclick="openAddLessonModal()" title="Добавить урок">+</button></th>';
                }
                datesHtml += '<th class="col-average"></th>';
                if(gradesDateHeaderRow) gradesDateHeaderRow.innerHTML = datesHtml;

                // Рендер заголовков с номерами (СТРОКА 2 - НОМЕРА)
                let numbersHtml = '<th class="col-drag"></th><th class="col-number">№</th><th class="col-student">Ф.И. учащегося</th>';
                data.lessons.forEach(l => {
                    numbersHtml += '<th class="lesson-number-header">' + l.lesson_number + '</th>';
                });
                if(editable) {
                    numbersHtml += '<th class="lesson-number-header col-add-lesson"><button class="btn-add-col" onclick="openAddLessonModal()" title="Добавить урок">+</button></th>';
                }
                numbersHtml += '<th class="col-average">Средний балл</th>';
                if(gradesNumberHeaderRow) gradesNumberHeaderRow.innerHTML = numbersHtml;

                // Рендер строк учеников с оценками
                let gradesHtml = '';
                data.students.forEach((s, idx) => {
                    const position = idx + 1;
                    gradesHtml += '<tr data-id="' + s.id + '" draggable="' + editable + '">';

                    // Drag handle
                    gradesHtml += '<td class="col-drag"><span class="drag-handle">' + (editable ? '↕' : '') + '</span></td>';

                    // Номер
                    gradesHtml += '<td class="col-number">' + position + '</td>';

                    // ФИО + кнопки управления
                    let nameHtml = '<span class="student-name">' + escapeHtml(s.full_name) + '</span>';
                    if(canManageStudents){
                        nameHtml += '<span class="student-actions">';
                        nameHtml += '<button class="btn-edit" onclick="editStudent(' + s.id + ',\'' + escapeHtml(s.full_name).replace(/'/g,"\\'") + '\')" title="Редактировать">✎</button>';
                        nameHtml += '<button class="btn-delete" onclick="deleteStudent(' + s.id + ')" title="Удалить">🗑</button>';
                        nameHtml += '</span>';
                    }
                    gradesHtml += '<td class="col-student">' + nameHtml + '</td>';

                    // Оценки (0-100 баллов) с комментариями
                    data.lessons.forEach(l => {
                        const gradeData = data.grades[s.id]?.[l.id];
                        const g = gradeData?.grade;
                        const comment = gradeData?.comment || '';
                        const hasGrade = (g !== undefined && g !== null && g !== '');
                        const cls = 'grade-cell' + (hasGrade ? ' has-grade' : ' empty') + (editable ? ' editable' : '');

                        // Tooltip с комментарием при наведении
                        let tooltip = comment ? ' title="📝 ' + escapeHtml(comment) + '"' : '';
                        const onclick = editable ? 'openGradeModal(' + s.id + ',' + l.id + ',' + (g !== null ? g : 'null') + ',\'' + escapeHtml(comment).replace(/'/g,"\\'") + '\')' : '';
                        gradesHtml += '<td class="' + cls + '" data-sid="' + s.id + '" data-lid="' + l.id + '" onclick="' + onclick + '"' + tooltip + '>' +
                            (hasGrade ? Math.round(g) : '-') + '</td>';
                    });

                    if(editable) {
                        gradesHtml += '<td class="grade-cell empty col-add-lesson"></td>';
                    } else if(data.lessons.length === 0) {
                        gradesHtml += '<td class="grade-cell empty">-</td>';
                    }

                    // Средний балл (округлённый)
                    const avg = data.averages[s.id];
                    gradesHtml += '<td class="col-average">' + (avg !== '-' ? Math.round(avg) : '-') + '</td></tr>';
                });
                gradesBody.innerHTML = gradesHtml;

                // === ТАБЛИЦА 2: ИНФОРМАЦИЯ ОБ УРОКАХ ===
                let lessonsHtml = '';
                data.lessons.forEach(l => {
                    lessonsHtml += '<tr data-lesson-id="' + l.id + '">';
                    lessonsHtml += '<td class="col-number">' + l.lesson_number + '</td>';
                    lessonsHtml += '<td class="col-date">' + formatDate(l.date) + '</td>';
                    lessonsHtml += '<td class="col-topic" title="' + escapeHtml(l.topic || '') + '">' + truncateText(l.topic || '-', 60) + '</td>';
                    lessonsHtml += '<td class="col-task" title="' + escapeHtml(l.individual_task || '') + '">' + truncateText(l.individual_task || '-', 40) + '</td>';

                    // Кнопка редактирования (для учителей)
                    if(editable){
                        lessonsHtml += '<td class="col-actions">';
                        lessonsHtml += '<button class="btn-edit" onclick="openEditLessonModal(' + l.id + ',\'' + (l.date || '') + '\',\'' + escapeHtml(l.topic || '').replace(/'/g,"\\'") + '\',\'' + escapeHtml(l.individual_task || '').replace(/'/g,"\\'") + '\')" title="Редактировать">✎</button>';
                        lessonsHtml += '</td>';
                    } else {
                        lessonsHtml += '<td class="col-actions">-</td>';
                    }
                    lessonsHtml += '</tr>';
                });
                lessonsBody.innerHTML = lessonsHtml;

                // Инициализация Drag'n'Drop для Таблицы 1
                if(editable) initDragDrop();
            })
            .catch(e => {
                console.error('Load error:', e);
                gradesBody.innerHTML = '<tr><td colspan="100" style="padding:40px;text-align:center;color:#dc3545;">Ошибка: ' + e.message + '</td></tr>';
                lessonsBody.innerHTML = '<tr><td colspan="5" style="padding:40px;text-align:center;color:#dc3545;">Ошибка: ' + e.message + '</td></tr>';
            });
    }

    // === ОЦЕНКИ (0-100 БАЛЛОВ) ===
    function openGradeModal(sid, lid, grade, comment){
        document.getElementById('gradeStudentId').value = sid;
        document.getElementById('gradeLessonId').value = lid;
        document.getElementById('gradeValue').value = (grade !== null && grade !== undefined && grade !== 'null') ? Math.round(grade) : '';
        document.getElementById('gradeComment').value = comment || '';
        gradeModal.style.display = 'block';
    }

    function saveGrade(){
        const data = {
            student_id: document.getElementById('gradeStudentId').value,
            lesson_id: document.getElementById('gradeLessonId').value,
            grade: document.getElementById('gradeValue').value,
            comment: document.getElementById('gradeComment').value
        };

        // ✅ Проверка 0-100 баллов
        if(!data.grade || data.grade < 0 || data.grade > 100) {
            showNotification('Баллы должны быть от 0 до 100', 'error');
            return;
        }

        fetch('api/save_grade.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    gradeModal.style.display = 'none';
                    loadJournalData();
                    showNotification('Оценка сохранена', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === РЕДАКТИРОВАНИЕ ДАТЫ УРОКА (БЫСТРОЕ) ===
    function openEditDateModal(lessonId, date){
        document.getElementById('editDateLessonId').value = lessonId;
        document.getElementById('editDateValue').value = date || '';
        editDateModal.style.display = 'block';
    }

    function saveEditDate(){
        const data = {
            lesson_id: parseInt(document.getElementById('editDateLessonId').value),
            date: document.getElementById('editDateValue').value
        };

        if(!data.lesson_id || !data.date) {
            showNotification('Укажите дату урока', 'error');
            return;
        }

        fetch('api/edit_lesson.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    editDateModal.style.display = 'none';
                    loadJournalData();
                    showNotification('Дата обновлена', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Edit date error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === УДАЛЕНИЕ УРОКА ===
    function deleteLesson(lessonId){
        if(!confirm('Вы уверены, что хотите удалить этот урок?\nВсе оценки учеников за этот урок будут удалены.\nЭто действие нельзя отменить.')) return;

        fetch('api/delete_lesson.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lesson_id: lessonId })
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    loadJournalData();
                    showNotification('Урок удалён', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Delete lesson error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === УПРАВЛЕНИЕ ПРЕДМЕТАМИ ===
    function toggleSubjectsPanel(){
        if(!subjectsPanel) return;
        const isVisible = subjectsPanel.style.display !== 'none';
        if(classesPanel) classesPanel.style.display = 'none';
        if(usersPanel) usersPanel.style.display = 'none';
        subjectsPanel.style.display = isVisible ? 'none' : 'block';
        if(!isVisible) loadSubjectsTable();
    }

    function loadSubjectsTable(){
        if(!subjectsBody) return;
        subjectsBody.innerHTML = '<tr><td colspan="4" style="padding:40px;text-align:center;color:#666;">Загрузка...</td></tr>';

        fetch('api/get_subjects.php?class_id=' + (currentClassId || ''))
            .then(r => r.json())
            .then(data => {
                if(!data.success) {
                    subjectsBody.innerHTML = '<tr><td colspan="4" style="padding:40px;text-align:center;color:#dc3545;">Ошибка: ' + (data.error || 'Неизвестная') + '</td></tr>';
                    return;
                }
                let html = '';
                data.subjects.forEach((s, idx) => {
                    html += '<tr>';
                    html += '<td class="col-number">' + (idx + 1) + '</td>';
                    html += '<td>' + escapeHtml(s.name) + '</td>';
                    html += '<td>' + escapeHtml(s.class_name || '—') + '</td>';
                    html += '<td class="col-actions">';
                    html += '<button class="btn-delete" onclick="deleteSubject(' + s.id + ',\'' + escapeHtml(s.name).replace(/'/g,"\\'") + '\')" title="Удалить предмет">🗑</button>';
                    html += '</td></tr>';
                });
                subjectsBody.innerHTML = html || '<tr><td colspan="4" style="padding:40px;text-align:center;color:#999;">Нет предметов</td></tr>';
            })
            .catch(err => {
                console.error('Load subjects error:', err);
                subjectsBody.innerHTML = '<tr><td colspan="4" style="padding:40px;text-align:center;color:#dc3545;">Ошибка загрузки</td></tr>';
            });
    }

    function openSubjectModal(preselectedClassId){
        if(!subjectModal) return;
        const classSelectEl = document.getElementById('subjectClassId');
        if(classSelectEl) {
            fetch('api/get_classes.php')
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        let options = '<option value="">Выберите класс</option>';
                        data.classes.forEach(c => {
                            const selected = preselectedClassId && c.id == preselectedClassId ? ' selected' : '';
                            options += '<option value="' + c.id + '"' + selected + '>' + escapeHtml(c.name) + '</option>';
                        });
                        classSelectEl.innerHTML = options;
                    }
                });
        }
        document.getElementById('subjectForm').reset();
        subjectModal.style.display = 'block';
    }

    function addSubject(){
        const data = {
            name: document.getElementById('subjectName').value,
            class_id: document.getElementById('subjectClassId').value
        };

        if(!data.name || !data.class_id) {
            showNotification('Заполните все обязательные поля', 'error');
            return;
        }

        fetch('api/add_subject.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    subjectModal.style.display = 'none';
                    // Если добавили для текущего класса — обновляем селект предметов
                    if(data.class_id == currentClassId) {
                        loadSubjectsForClass(currentClassId);
                    }
                    loadSubjectsTable();
                    showNotification('Предмет создан', 'success');
                    subjectForm.reset();
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Add subject error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    function deleteSubject(id, name){
        if(!confirm('Вы уверены, что хотите удалить предмет "' + name + '"?\nВсе уроки и оценки этого предмета будут удалены.\nЭто действие нельзя отменить.')) return;

        fetch('api/delete_subject.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    loadSubjectsForClass(currentClassId);
                    loadSubjectsTable();
                    if(id == currentSubject) {
                        currentSubject = '';
                        loadJournalData();
                    }
                    showNotification('Предмет удалён', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Delete subject error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === УПРАВЛЕНИЕ КЛАССАМИ ===
    function toggleClassesPanel(){
        if(!classesPanel) return;
        const isVisible = classesPanel.style.display !== 'none';
        // Скрываем другие панели
        if(subjectsPanel) subjectsPanel.style.display = 'none';
        if(usersPanel) usersPanel.style.display = 'none';
        classesPanel.style.display = isVisible ? 'none' : 'block';
        if(!isVisible) loadClassesTable();
    }

    function loadClassesTable(){
        if(!classesBody) return;
        classesBody.innerHTML = '<tr><td colspan="3" style="padding:40px;text-align:center;color:#666;">Загрузка...</td></tr>';

        fetch('api/get_classes.php')
            .then(r => r.json())
            .then(data => {
                if(!data.success) {
                    classesBody.innerHTML = '<tr><td colspan="3" style="padding:40px;text-align:center;color:#dc3545;">Ошибка: ' + (data.error || 'Неизвестная') + '</td></tr>';
                    return;
                }
                let html = '';
                data.classes.forEach((c, idx) => {
                    html += '<tr>';
                    html += '<td class="col-number">' + (idx + 1) + '</td>';
                    html += '<td>' + escapeHtml(c.name) + '</td>';
                    html += '<td class="col-actions">';
                    html += '<button class="btn-delete" onclick="deleteClass(' + c.id + ',\'' + escapeHtml(c.name).replace(/'/g,"\\'") + '\')" title="Удалить класс">🗑</button>';
                    html += '</td></tr>';
                });
                classesBody.innerHTML = html || '<tr><td colspan="3" style="padding:40px;text-align:center;color:#999;">Нет классов</td></tr>';
            })
            .catch(err => {
                console.error('Load classes error:', err);
                classesBody.innerHTML = '<tr><td colspan="3" style="padding:40px;text-align:center;color:#dc3545;">Ошибка загрузки</td></tr>';
            });
    }

    function addClass(){
        const data = {
            name: document.getElementById('className').value
        };

        if(!data.name) {
            showNotification('Введите название класса', 'error');
            return;
        }

        fetch('api/add_class.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    classModal.style.display = 'none';
                    loadClasses(); // обновляем селект классов
                    loadClassesTable(); // обновляем таблицу классов
                    showNotification('Класс создан', 'success');
                    classForm.reset();
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Add class error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    function deleteClass(id, name){
        if(!confirm('Вы уверены, что хотите удалить класс "' + name + '"?\nВсе ученики, предметы, уроки и оценки этого класса будут удалены.\nЭто действие нельзя отменить.')) return;

        fetch('api/delete_class.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    loadClasses(); // обновляем селект
                    loadClassesTable(); // обновляем таблицу
                    // Если удалили текущий класс — сбрасываем
                    if(id == currentClassId) {
                        currentClassId = '';
                        currentSubject = '';
                        loadJournalData();
                    }
                    showNotification('Класс удалён', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Delete class error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === УПРАВЛЕНИЕ ПОЛЬЗОВАТЕЛЯМИ ===
    let usersData = { subjects: [], classes: [] };

    function toggleUsersPanel(){
        if(!usersPanel) return;
        const isVisible = usersPanel.style.display !== 'none';
        if(subjectsPanel) subjectsPanel.style.display = 'none';
        if(classesPanel) classesPanel.style.display = 'none';
        usersPanel.style.display = isVisible ? 'none' : 'block';
        if(!isVisible) loadUsers();
    }

    function loadUsers(){
        if(!usersBody) return;
        usersBody.innerHTML = '<tr><td colspan="7" style="padding:40px;text-align:center;color:#666;">Загрузка...</td></tr>';

        fetch('api/get_users.php')
            .then(r => r.json())
            .then(data => {
                if(!data.success) {
                    usersBody.innerHTML = '<tr><td colspan="7" style="padding:40px;text-align:center;color:#dc3545;">Ошибка: ' + (data.error || 'Неизвестная') + '</td></tr>';
                    return;
                }
                usersData.subjects = data.subjects || [];
                usersData.classes = data.classes || [];

                let html = '';
                data.users.forEach((u, idx) => {
                    const roleLabel = u.role === 'teacher' ? 'Педагог' : (u.role === 'methodologist' ? 'Методист' : u.role);
                    // Формируем строку назначений
                    let assignmentsStr = '—';
                    if(u.assignments && u.assignments.length > 0) {
                        assignmentsStr = u.assignments.map(a => escapeHtml(a.class_name + ': ' + a.subject_name)).join('<br>');
                    }
                    html += '<tr>';
                    html += '<td class="col-number">' + (idx + 1) + '</td>';
                    html += '<td>' + escapeHtml(u.full_name || '-') + '</td>';
                    html += '<td>' + escapeHtml(u.username || '-') + '</td>';
                    html += '<td>' + roleLabel + '</td>';
                    html += '<td colspan="2" style="font-size:12px;">' + assignmentsStr + '</td>';
                    html += '<td class="col-actions">';
                    html += '<button class="btn-edit" onclick="editUser(' + u.id + ',\'' + escapeHtml(u.full_name).replace(/'/g,"\\'") + '\',' + JSON.stringify(u.assignments || []).replace(/"/g,'&quot;') + ')" title="Редактировать">✎</button>';
                    html += '<button class="btn-delete" onclick="deleteUser(' + u.id + ',\'' + escapeHtml(u.full_name).replace(/'/g,"\\'") + '\')" title="Удалить">🗑</button>';
                    html += '</td></tr>';
                });
                usersBody.innerHTML = html || '<tr><td colspan="7" style="padding:40px;text-align:center;color:#999;">Нет пользователей</td></tr>';
            })
            .catch(err => {
                console.error('Load users error:', err);
                usersBody.innerHTML = '<tr><td colspan="7" style="padding:40px;text-align:center;color:#dc3545;">Ошибка загрузки</td></tr>';
            });
    }

    // Динамические назначения (класс + предмет)
    function addAssignmentRow(containerId, selectedClassId, selectedSubjectId){
        const container = document.getElementById(containerId);
        if(!container) return;
        const rowId = 'assign_' + Date.now() + '_' + Math.random().toString(36).substr(2,5);

        const classOptions = '<option value="">Класс</option>' + usersData.classes.map(c => '<option value="' + c.id + '"' + (c.id == selectedClassId ? ' selected' : '') + '>' + escapeHtml(c.name) + '</option>').join('');
        const subjectOptions = '<option value="">Предмет</option>' + usersData.subjects.map(s => '<option value="' + s.id + '"' + (s.id == selectedSubjectId ? ' selected' : '') + '>' + escapeHtml(s.name) + '</option>').join('');

        const row = document.createElement('div');
        row.className = 'assignment-row';
        row.id = rowId;
        row.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:center;';
        row.innerHTML =
            '<select class="assign-class" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;">' + classOptions + '</select>' +
            '<select class="assign-subject" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;">' + subjectOptions + '</select>' +
            '<button type="button" class="btn-delete" onclick="document.getElementById(\'' + rowId + '\').remove()" title="Удалить">×</button>';
        container.appendChild(row);
    }

    function collectAssignments(containerId){
        const container = document.getElementById(containerId);
        if(!container) return [];
        const rows = container.querySelectorAll('.assignment-row');
        const assignments = [];
        rows.forEach(row => {
            const classId = row.querySelector('.assign-class').value;
            const subjectId = row.querySelector('.assign-subject').value;
            if(classId && subjectId) {
                assignments.push({ class_id: parseInt(classId), subject_id: parseInt(subjectId) });
            }
        });
        return assignments;
    }

    // Показ/скрытие блока назначений при смене роли
    const userRoleSelect = document.getElementById('userRole');
    if(userRoleSelect) {
        userRoleSelect.addEventListener('change', function(){
            const block = document.getElementById('userAssignmentsBlock');
            if(block) {
                block.style.display = this.value === 'teacher' ? 'block' : 'none';
                if(this.value === 'teacher' && document.getElementById('userAssignmentsList').children.length === 0) {
                    addAssignmentRow('userAssignmentsList');
                }
            }
        });
    }

    function openUserModal(){
        if(!userModal) return;
        document.getElementById('userAssignmentsList').innerHTML = '';
        document.getElementById('userAssignmentsBlock').style.display = 'none';
        document.getElementById('userForm').reset();
        userModal.style.display = 'block';
    }

    function addUser(){
        const data = {
            role: document.getElementById('userRole').value,
            full_name: document.getElementById('userFullName').value,
            username: document.getElementById('userUsername').value,
            password: document.getElementById('userPassword').value,
            email: document.getElementById('userEmail').value,
            assignments: collectAssignments('userAssignmentsList')
        };

        if(!data.role || !data.full_name || !data.username || !data.password) {
            showNotification('Заполните обязательные поля', 'error');
            return;
        }

        if(data.password.length < 6) {
            showNotification('Пароль должен быть не менее 6 символов', 'error');
            return;
        }

        // Создаём пользователя
        fetch('api/add_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if(res.success && data.role === 'teacher' && data.assignments.length > 0) {
                    // Сохраняем назначения
                    return fetch('api/update_teacher_assignments.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ teacher_id: res.id, assignments: data.assignments })
                    }).then(() => res);
                }
                return res;
            })
            .then(res => {
                if(res.success){
                    userModal.style.display = 'none';
                    loadUsers();
                    showNotification('Пользователь создан', 'success');
                    userForm.reset();
                    document.getElementById('userAssignmentsList').innerHTML = '';
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Add user error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    let currentEditUserId = null;

    function editUser(id, fullName, assignments){
        if(!editUserModal) return;
        currentEditUserId = id;
        document.getElementById('editUserId').value = id;
        document.getElementById('editUserFullName').value = fullName || '';
        document.getElementById('editUserPassword').value = '';

        // Заполняем назначения
        const list = document.getElementById('editAssignmentsList');
        list.innerHTML = '';
        if(assignments && assignments.length > 0) {
            assignments.forEach(a => addAssignmentRow('editAssignmentsList', a.class_id, a.subject_id));
        } else {
            addAssignmentRow('editAssignmentsList');
        }

        editUserModal.style.display = 'block';
    }

    function saveEditUser(){
        const data = {
            id: parseInt(document.getElementById('editUserId').value),
            full_name: document.getElementById('editUserFullName').value,
            password: document.getElementById('editUserPassword').value
        };
        const assignments = collectAssignments('editAssignmentsList');

        if(!data.id || !data.full_name) {
            showNotification('Заполните обязательные поля', 'error');
            return;
        }

        fetch('api/edit_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if(res.success && assignments.length > 0) {
                    return fetch('api/update_teacher_assignments.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ teacher_id: data.id, assignments: assignments })
                    }).then(() => res);
                }
                return res;
            })
            .then(res => {
                if(res.success){
                    editUserModal.style.display = 'none';
                    loadUsers();
                    showNotification('Пользователь обновлён', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Edit user error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    function deleteUser(id, fullName){
        if(!confirm('Вы уверены, что хотите удалить пользователя "' + fullName + '"?\nЭто действие нельзя отменить.')) return;

        fetch('api/delete_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    loadUsers();
                    showNotification('Пользователь удалён', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Delete user error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === ЛИЧНЫЙ КАБИНЕТ (ПРОФИЛЬ) ===
    function openProfileModal(){
        if(!profileModal) return;
        const body = document.body;
        document.getElementById('profileUsername').value = body.dataset.username || '';
        document.getElementById('profileRole').value = body.dataset.roleLabel || '';
        document.getElementById('profileFullName').value = body.dataset.fullName || '';
        document.getElementById('profileEmail').value = body.dataset.email || '';
        document.getElementById('profileCurrentPassword').value = '';
        document.getElementById('profileNewPassword').value = '';
        profileModal.style.display = 'block';
    }

    function saveProfile(){
        const data = {
            full_name: document.getElementById('profileFullName').value,
            email: document.getElementById('profileEmail').value,
            current_password: document.getElementById('profileCurrentPassword').value,
            password: document.getElementById('profileNewPassword').value
        };

        if(!data.full_name) {
            showNotification('Введите Ф.И.О.', 'error');
            return;
        }

        // Если введён новый пароль — требуется текущий
        if(data.password && !data.current_password) {
            showNotification('Введите текущий пароль для смены', 'error');
            return;
        }

        fetch('api/update_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    profileModal.style.display = 'none';
                    showNotification('Профиль обновлён', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Update profile error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === РЕДАКТИРОВАНИЕ УРОКА (ДАТА + ТЕМА + ЗАДАНИЕ) ===
    function openEditLessonModal(lessonId, date, topic, task){
        document.getElementById('editLessonId').value = lessonId;
        document.getElementById('editLessonDate').value = date || '';
        document.getElementById('editLessonTopic').value = topic || '';
        document.getElementById('editLessonTask').value = task || '';
        editLessonModal.style.display = 'block';
    }

    function saveEditLesson(){
        const data = {
            lesson_id: parseInt(document.getElementById('editLessonId').value),
            date: document.getElementById('editLessonDate').value,
            topic: document.getElementById('editLessonTopic').value,
            individual_task: document.getElementById('editLessonTask').value
        };

        if(!data.lesson_id || !data.date || !data.topic) {
            showNotification('Заполните все обязательные поля', 'error');
            return;
        }

        fetch('api/edit_lesson.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    editLessonModal.style.display = 'none';
                    loadJournalData();
                    showNotification('Урок обновлён', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Edit lesson error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === ДОБАВЛЕНИЕ УРОКА ===
    function addLesson(){
        const data = {
            subject_id: currentSubject,
            date: document.getElementById('lessonDate').value,
            topic: document.getElementById('lessonTopic').value,
            individual_task: document.getElementById('lessonTask').value,
            quarter: 1
        };

        if(!data.date) {
            showNotification('Укажите дату урока', 'error');
            document.getElementById('lessonDate').focus();
            return;
        }

        if(!data.topic) {
            showNotification('Укажите тему урока', 'error');
            document.getElementById('lessonTopic').focus();
            return;
        }

        fetch('api/add_lesson.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    lessonModal.style.display = 'none';
                    loadJournalData();
                    showNotification('Урок добавлен', 'success');
                    lessonForm.reset();
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Add lesson error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === УЧЕНИКИ ===
    function addStudent(){
        const name = prompt('Введите Ф.И.О. ученика:');
        if(!name || name.trim() === '') return;

        fetch('api/add_student.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ full_name: name.trim(), class_id: currentClassId || 1 })
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    loadJournalData();
                    showNotification('Ученик добавлен', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Add student error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    function editStudent(id, currentName){
        const newName = prompt('Редактировать Ф.И.О.:', currentName);
        if(!newName || newName.trim() === '' || newName === currentName) return;

        fetch('api/edit_student.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, full_name: newName.trim(), class_id: currentClassId || 1 })
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    loadJournalData();
                    showNotification('Ученик обновлён', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Edit student error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    function deleteStudent(id){
        if(!confirm('Вы уверены, что хотите удалить этого ученика?\nВсе оценки будут удалены.\nЭто действие нельзя отменить.')) return;

        fetch('api/delete_student.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, class_id: currentClassId || 1 })
        })
            .then(r => r.json())
            .then(res => {
                if(res.success){
                    loadJournalData();
                    showNotification('Ученик удалён', 'success');
                } else {
                    showNotification('Ошибка: ' + (res.error || 'Неизвестная'), 'error');
                }
            })
            .catch(err => {
                console.error('Delete student error:', err);
                showNotification('Ошибка сети', 'error');
            });
    }

    // === DRAG'N'DROP (Только Таблица 1) ===
    function initDragDrop(){
        const editable = (userRole === 'principal' || userRole === 'methodologist' || userRole === 'teacher');
        if(!editable) return;

        let dragged = null;

        gradesBody.addEventListener('dragstart', function(e){
            const row = e.target.closest('tr');
            if(row && !e.target.closest('.student-actions')){
                dragged = row;
                row.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            }
        });

        gradesBody.addEventListener('dragend', function(e){
            if(dragged){
                dragged.classList.remove('dragging');
                dragged = null;
            }
            document.querySelectorAll('.drag-over').forEach(r => r.classList.remove('drag-over'));
        });

        gradesBody.addEventListener('dragover', function(e){
            e.preventDefault();
            const row = e.target.closest('tr');
            if(row && row !== dragged) row.classList.add('drag-over');
        });

        gradesBody.addEventListener('dragleave', function(e){
            const row = e.target.closest('tr');
            if(row) row.classList.remove('drag-over');
        });

        gradesBody.addEventListener('drop', function(e){
            e.preventDefault();
            const row = e.target.closest('tr');
            if(row && dragged && row !== dragged){
                const rows = Array.from(gradesBody.querySelectorAll('tr'));
                if(rows.indexOf(dragged) < rows.indexOf(row)){
                    row.after(dragged);
                } else {
                    row.before(dragged);
                }
                renumberRows();
                updateOrder();
            }
            document.querySelectorAll('.drag-over').forEach(r => r.classList.remove('drag-over'));
        });
    }

    function renumberRows(){
        const rows = gradesBody.querySelectorAll('tr');
        rows.forEach((row, idx) => {
            const numCell = row.querySelector('.col-number');
            if(numCell) numCell.textContent = idx + 1;
        });
    }

    function updateOrder(){
        const order = Array.from(gradesBody.querySelectorAll('tr'))
            .map((r, i) => ({position: i, id: r.dataset.id}))
            .filter(item => item.id);

        if(order.length === 0) return;

        fetch('api/reorder_students.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({order: order})
        })
            .then(r => r.json())
            .then(res => {
                if(res.success) showNotification('Порядок сохранён', 'success');
            })
            .catch(() => {});
    }

    // === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===
    function formatDate(dateStr){
        if(!dateStr) return '';
        const d = new Date(dateStr);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        return day + '.' + month;
    }

    function escapeHtml(text){
        if(!text) return '';
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function truncateText(text, maxLength){
        if(!text) return '';
        return text.length <= maxLength ? text : text.substring(0, maxLength) + '...';
    }

    function showNotification(msg, type){
        const existing = document.querySelector('.notification');
        if(existing) existing.remove();

        const n = document.createElement('div');
        n.className = 'notification ' + type;
        n.textContent = msg;
        document.body.appendChild(n);
        setTimeout(() => {
            if(n.parentNode) n.parentNode.removeChild(n);
        }, 3000);
    }
});
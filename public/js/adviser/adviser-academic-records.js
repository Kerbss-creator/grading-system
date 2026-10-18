/* ADVISER ACADEMIC RECORDS */
document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('academicRecordsPage');

    if (!page) {
        return;
    }

    const subjectOverview = document.getElementById('subjectOverview');
    const studentRecordsView = document.getElementById('studentRecordsView');
    const subjectCards = Array.from(page.querySelectorAll('[data-subject]'));
    const selectedSubjectTitle = document.getElementById('selectedSubjectTitle');
    const backToSubjects = document.getElementById('backToSubjects');
    const termButtons = Array.from(page.querySelectorAll('[data-term]'));
    const studentSearch = document.getElementById('studentSearch');
    const studentRows = Array.from(page.querySelectorAll('#studentTableBody tr'));
    const studentTableBody = document.getElementById('studentTableBody');
    const studentCount = document.getElementById('studentCount');
    const studentListSubtitle = document.getElementById('studentListSubtitle');
    const studentEmptyState = document.getElementById('studentEmptyState');

    const gradesModal = document.getElementById('gradesModal');
    const closeGradesModal = document.getElementById('closeGradesModal');
    const closeGradesModalFooter = document.getElementById('closeGradesModalFooter');

    const modalStudentName = document.getElementById('modalStudentName');
    const modalStudentId = document.getElementById('modalStudentId');
    const modalSubject = document.getElementById('modalSubject');
    const modalTerm = document.getElementById('modalTerm');
    const modalFinalGrade = document.getElementById('modalFinalGrade');
    const modalRemarks = document.getElementById('modalRemarks');
    const gradesModalSubtitle = document.getElementById('gradesModalSubtitle');

    let currentSubject = '';
    let currentTerm = '1st Term';
    let lastFocusedElement = null;

    function getRowStudentData(row) {
        let grades = {};

        try {
            grades = JSON.parse(row.dataset.grades || '{}');
        } catch (error) {
            console.warn('Could not read sample grade data for this student.', error);
        }

        return {
            name: row.dataset.studentName || 'Unknown Student',
            id: row.dataset.studentId || '—',
            grades: grades
        };
    }

    function setActiveTerm(term) {
        currentTerm = term;

        termButtons.forEach(function (button) {
            const isActive = button.dataset.term === term;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });

        studentListSubtitle.textContent = currentTerm + ' · ' + currentSubject;
        filterStudents();
    }

    function filterStudents() {
        const query = (studentSearch.value || '').trim().toLowerCase();
        let visibleCount = 0;

        studentRows.forEach(function (row) {
            const name = (row.dataset.studentName || '').toLowerCase();
            const id = (row.dataset.studentId || '').toLowerCase();
            const matches = name.includes(query) || id.includes(query);

            row.hidden = !matches;

            if (matches) {
                visibleCount += 1;
            }
        });

        studentCount.textContent = visibleCount + (visibleCount === 1 ? ' student' : ' students');
        studentEmptyState.hidden = visibleCount !== 0;
        studentTableBody.closest('.ar-table-wrap').hidden = visibleCount === 0;
    }

    function openSubject(subjectName) {
        currentSubject = subjectName;
        selectedSubjectTitle.textContent = subjectName;
        subjectOverview.hidden = true;
        studentRecordsView.hidden = false;
        studentSearch.value = '';
        setActiveTerm('1st Term');
        studentSearch.focus({ preventScroll: true });
    }

    function showGrades(row, button) {
        const student = getRowStudentData(row);
        const termGrades = student.grades[currentTerm] || {};
        const grade = termGrades.grade;
        const remarks = termGrades.remarks;

        lastFocusedElement = button || document.activeElement;

        modalStudentName.textContent = student.name;
        modalStudentId.textContent = student.id;
        modalSubject.textContent = currentSubject;
        modalTerm.textContent = currentTerm;
        modalFinalGrade.textContent = grade === null || grade === undefined || grade === '' ? 'Not yet available' : grade;
        modalRemarks.textContent = remarks || 'Not yet available';
        gradesModalSubtitle.textContent = student.name + ' · ' + currentSubject + ' · ' + currentTerm;

        gradesModal.hidden = false;
        document.body.classList.add('ar-modal-open');
        closeGradesModal.focus();
    }

    function hideGradesModal() {
        gradesModal.hidden = true;
        document.body.classList.remove('ar-modal-open');

        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
        }
    }

    subjectCards.forEach(function (card) {
        card.addEventListener('click', function () {
            openSubject(card.dataset.subject || 'Subject');
        });
    });

    backToSubjects.addEventListener('click', function () {
        studentRecordsView.hidden = true;
        subjectOverview.hidden = false;
        currentSubject = '';
        studentSearch.value = '';
        filterStudents();
    });

    termButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            setActiveTerm(button.dataset.term);
        });
    });

    studentSearch.addEventListener('input', filterStudents);

    studentRows.forEach(function (row) {
        const button = row.querySelector('[data-view-grades]');

        if (button) {
            button.addEventListener('click', function () {
                showGrades(row, button);
            });
        }
    });

    closeGradesModal.addEventListener('click', hideGradesModal);
    closeGradesModalFooter.addEventListener('click', hideGradesModal);

    gradesModal.addEventListener('click', function (event) {
        if (event.target === gradesModal) {
            hideGradesModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !gradesModal.hidden) {
            hideGradesModal();
        }
    });

    filterStudents();
});

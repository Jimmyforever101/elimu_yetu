// Main JS for Elimu Yetu Student Progress Tracker

document.addEventListener('DOMContentLoaded', function() {
    // Example: Show a welcome toast if available
    if (window.showWelcomeToast) {
        showNotification('Welcome to Elimu Yetu!', 'info');
    }

    // Modal functionality
    window.showModal = function(title, message) {
        let modalHtml = `<div class="modal fade" id="customModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        let modal = new bootstrap.Modal(document.getElementById('customModal'));
        modal.show();
        document.getElementById('customModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('customModal').remove();
        });
    };

    // Notification functionality
    window.showNotification = function(message, type = 'info') {
        let notif = document.createElement('div');
        notif.className = `alert alert-${type}`;
        notif.style.position = 'fixed';
        notif.style.top = '20px';
        notif.style.right = '20px';
        notif.style.zIndex = '9999';
        notif.innerHTML = message;
        document.body.appendChild(notif);
        setTimeout(() => notif.remove(), 3000);
    };

    // AJAX example: fetch courses
    window.fetchCourses = function(callback) {
        fetch('admin/courses.php?ajax=1')
            .then(response => response.json())
            .then(data => callback(data))
            .catch(() => showNotification('Failed to fetch courses', 'danger'));
    };

    // Example: Select/Deselect all checkboxes for bulk actions
    document.querySelectorAll('#select_all').forEach(function(selectAll) {
        selectAll.addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('input[type="checkbox"].select_course, input[type="checkbox"].select_lesson, input[type="checkbox"].select_student');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    });

    // Add more JS features as needed
});

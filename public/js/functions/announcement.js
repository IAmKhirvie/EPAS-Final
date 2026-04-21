// public/js/announcements.js
document.addEventListener('DOMContentLoaded', function() {
    // Load announcements for navbar
    loadNavbarAnnouncements();
    
    // Set up periodic refresh (every 30 seconds)
    setInterval(loadNavbarAnnouncements, 30000);
    
    // Quick comment toggle
    document.querySelectorAll('.announcement-item').forEach(item => {
        const commentBtn = item.querySelector('.btn-outline-primary');
        const commentForm = item.querySelector('.quick-comment');
        
        if (commentBtn && commentForm) {
            commentBtn.addEventListener('click', function(e) {
                if (!e.target.closest('a')) {
                    e.preventDefault();
                    commentForm.style.display = commentForm.style.display === 'none' ? 'block' : 'none';
                }
            });
        }
    });
});

// public/js/announcements.js

function loadNavbarAnnouncements() {
    fetch('/api/announcements/recent')
        .then(response => response.json())
        .then(announcements => {
            const notificationsList = document.getElementById('notifications-list');
            const badge = document.getElementById('notification-badge');
            
            if (announcements.length > 0) {
                badge.textContent = announcements.length;
                badge.style.display = 'inline-block';
                
                notificationsList.innerHTML = announcements.map(announcement => `
                    <div class="notification-item" data-announcement-id="${announcement.id}">
                        <div class="notification-content">
                            <div class="notification-title d-flex align-items-center">
                                ${announcement.is_urgent ? '<span class="badge bg-danger me-2">URGENT</span>' : ''}
                                <strong>${announcement.title}</strong>
                            </div>
                            <div class="notification-message">
                                ${announcement.content}
                            </div>
                            <div class="notification-meta">
                                <small class="text-muted">
                                    By ${announcement.author} • ${announcement.created_at}
                                </small>
                            </div>
                        </div>
                        <div class="notification-actions">
                            <a href="${announcement.url}" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                    </div>
                `).join('');
            } else {
                badge.style.display = 'none';
                notificationsList.innerHTML = `
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-bell-slash fa-lg mb-2"></i>
                        <p>No announcements</p>
                    </div>
                `;
            }
        })
        .catch(error => console.error('Error loading announcements:', error));
}
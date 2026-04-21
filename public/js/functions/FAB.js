// Universal FAB Script
document.addEventListener('DOMContentLoaded', function() {
    const fabContainer = document.getElementById('fabContainer');
    const fabMain = document.getElementById('fabMain');
    const fabOptions = document.querySelectorAll('.fab-option');
    const overlay = document.getElementById('overlay');
    const fabBackdrop = document.getElementById('fab-backdrop');

    // Sidebar elements
    const createCourseSidebar = document.getElementById('createCourseSidebar');
    const createModuleSidebar = document.getElementById('createModuleSidebar');
    const createAnnouncementSidebar = document.getElementById('createAnnouncementSidebar');
    const addUserSidebar = document.getElementById('addUserSidebar');

    // Close buttons
    const closeCourseSidebar = document.getElementById('closeCourseSidebar');
    const closeModuleSidebar = document.getElementById('closeModuleSidebar');
    const closeAnnouncementSidebar = document.getElementById('closeAnnouncementSidebar');
    const closeUserSidebar = document.getElementById('closeSidebar');

    // Only initialize if FAB exists (user is not student)
    if (fabContainer && fabMain) {

        // Add ARIA attributes for accessibility
        fabMain.setAttribute('aria-label', 'Open actions menu');
        fabMain.setAttribute('aria-expanded', 'false');
        fabMain.setAttribute('aria-haspopup', 'true');

        // Add ARIA labels to FAB option buttons
        var fabOptionCourse = document.getElementById('fabOptionCourse');
        var fabOptionModule = document.getElementById('fabOptionModule');
        var fabOptionAnnouncement = document.getElementById('fabOptionAnnouncement');
        var fabOptionEnroll = document.getElementById('fabOptionEnroll');
        if (fabOptionCourse) fabOptionCourse.setAttribute('aria-label', 'Create Course');
        if (fabOptionModule) fabOptionModule.setAttribute('aria-label', 'Create Module');
        if (fabOptionAnnouncement) fabOptionAnnouncement.setAttribute('aria-label', 'Create Announcement');
        if (fabOptionEnroll) fabOptionEnroll.setAttribute('aria-label', 'Add User');

        // Helper to update aria-expanded state
        function updateFabAriaState() {
            var isActive = fabContainer.classList.contains('active');
            fabMain.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            fabMain.setAttribute('aria-label', isActive ? 'Close actions menu' : 'Open actions menu');
        }

        // Toggle FAB options and backdrop
        fabMain.addEventListener('click', function(e) {
            e.stopPropagation();
            fabContainer.classList.toggle('active');
            updateFabAriaState();

            if (fabBackdrop) {
                if (fabContainer.classList.contains('active')) {
                    fabBackdrop.classList.add('active');
                } else {
                    fabBackdrop.classList.remove('active');
                }
            }
        });

        // Keyboard support: Enter/Space to toggle FAB
        fabMain.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                fabMain.click();
            }
        });

        // Handle all FAB option clicks
        fabOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();

                // Close FAB options and backdrop
                fabContainer.classList.remove('active');
                updateFabAriaState();
                if (fabBackdrop) {
                    fabBackdrop.classList.remove('active');
                }

                const optionId = this.id;

                switch(optionId) {
                    case 'fabOptionCourse':
                        if (createCourseSidebar) {
                            openSidebar(createCourseSidebar);
                        } else {
                            window.location.href = '/courses/create';
                        }
                        break;
                    case 'fabOptionModule':
                        if (createModuleSidebar) {
                            openSidebar(createModuleSidebar);
                        } else {
                            window.location.href = '/modules/create';
                        }
                        break;
                    case 'fabOptionAnnouncement':
                        if (createAnnouncementSidebar) {
                            openSidebar(createAnnouncementSidebar);
                        } else {
                            window.location.href = '/announcements/create';
                        }
                        break;
                    case 'fabOptionEnroll':
                        if (addUserSidebar) {
                            openSidebar(addUserSidebar);
                        } else {
                            window.location.href = '/private/users';
                        }
                        break;
                }
            });
        });

        // Generic sidebar open function
        function openSidebar(sidebar) {
            if (sidebar) {
                sidebar.classList.add('active');
                overlay.classList.add('active');
                document.body.classList.add('sidebar-open');

                // Hide FAB when slide panel is open to avoid overlap
                if (fabContainer) {
                    fabContainer.style.display = 'none';
                }
            }
        }

        // Generic sidebar close function
        function closeSidebarFunc(sidebar) {
            if (sidebar) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');

                // Restore FAB visibility
                if (fabContainer) {
                    fabContainer.style.display = '';
                }
            }
        }

        // Close sidebars when close buttons are clicked
        if (closeCourseSidebar && createCourseSidebar) {
            closeCourseSidebar.addEventListener('click', function(e) {
                e.stopPropagation();
                closeSidebarFunc(createCourseSidebar);
            });
        }

        if (closeModuleSidebar && createModuleSidebar) {
            closeModuleSidebar.addEventListener('click', function(e) {
                e.stopPropagation();
                closeSidebarFunc(createModuleSidebar);
            });
        }

        if (closeAnnouncementSidebar && createAnnouncementSidebar) {
            closeAnnouncementSidebar.addEventListener('click', function(e) {
                e.stopPropagation();
                closeSidebarFunc(createAnnouncementSidebar);
            });
        }

        if (closeUserSidebar && addUserSidebar) {
            closeUserSidebar.addEventListener('click', function(e) {
                e.stopPropagation();
                closeSidebarFunc(addUserSidebar);
            });
        }

        // Close sidebars when overlay is clicked
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeAllSidebars();
            }
        });

        // Close FAB and backdrop when clicking on backdrop
        if (fabBackdrop) {
            fabBackdrop.addEventListener('click', function() {
                fabContainer.classList.remove('active');
                updateFabAriaState();
                fabBackdrop.classList.remove('active');
            });
        }

        // Close FAB options when clicking outside
        document.addEventListener('click', function(e) {
            if (fabContainer && !fabContainer.contains(e.target)) {
                fabContainer.classList.remove('active');
                updateFabAriaState();
                if (fabBackdrop) {
                    fabBackdrop.classList.remove('active');
                }
            }
        });

        // Close sidebars with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAllSidebars();
                fabContainer.classList.remove('active');
                updateFabAriaState();
                if (fabBackdrop) {
                    fabBackdrop.classList.remove('active');
                }
            }
        });

        // Function to close all sidebars
        function closeAllSidebars() {
            if (createCourseSidebar) createCourseSidebar.classList.remove('active');
            if (createModuleSidebar) createModuleSidebar.classList.remove('active');
            if (createAnnouncementSidebar) createAnnouncementSidebar.classList.remove('active');
            if (addUserSidebar) addUserSidebar.classList.remove('active');

            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');

            // Restore FAB visibility
            if (fabContainer) {
                fabContainer.style.display = '';
            }
        }

        // Close sidebars when forms are submitted successfully
        const addUserForm = document.getElementById('addUserForm');
        if (addUserForm && addUserSidebar) {
            addUserForm.addEventListener('submit', function() {
                setTimeout(function() {
                    closeSidebarFunc(addUserSidebar);
                }, 1000);
            });
        }

        const createModuleForm = document.getElementById('createModuleForm');
        if (createModuleForm && createModuleSidebar) {
            createModuleForm.addEventListener('submit', function() {
                setTimeout(function() {
                    closeSidebarFunc(createModuleSidebar);
                }, 1000);
            });
        }

        const createCourseForm = document.getElementById('createCourseForm');
        if (createCourseForm && createCourseSidebar) {
            createCourseForm.addEventListener('submit', function() {
                setTimeout(function() {
                    closeSidebarFunc(createCourseSidebar);
                }, 1000);
            });
        }

        const createAnnouncementForm = document.getElementById('createAnnouncementForm');
        if (createAnnouncementForm && createAnnouncementSidebar) {
            createAnnouncementForm.addEventListener('submit', function() {
                setTimeout(function() {
                    closeSidebarFunc(createAnnouncementSidebar);
                }, 1000);
            });
        }

        // Auto-set module order when course is selected
        const fabCourseSelect = document.getElementById('fab_course_id');
        const fabModuleOrder = document.getElementById('fab_module_order');

        if (fabCourseSelect && fabModuleOrder) {
            fabCourseSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const moduleCount = parseInt(selectedOption.getAttribute('data-module-count') || 0);
                fabModuleOrder.value = moduleCount + 1;
            });
        }

        // Role-based field display in add user form
        const roleSelect = document.getElementById('role');
        const studentFields = document.getElementById('student-fields');
        const instructorFields = document.getElementById('instructor-fields');

        function toggleFieldsBasedOnRole() {
            if (!roleSelect) return;

            const role = roleSelect.value;

            if (studentFields) {
                studentFields.style.display = role === 'student' ? '' : 'none';
            }
            if (instructorFields) {
                instructorFields.style.display = role === 'instructor' ? '' : 'none';
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', toggleFieldsBasedOnRole);
            toggleFieldsBasedOnRole();
        }

        // Close FAB options when window loses focus
        window.addEventListener('blur', function() {
            fabContainer.classList.remove('active');
            updateFabAriaState();
            if (fabBackdrop) {
                fabBackdrop.classList.remove('active');
            }
        });
    }
});

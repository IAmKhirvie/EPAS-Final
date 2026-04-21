document.addEventListener('DOMContentLoaded', function() {

    // Enhanced Search functionality - search after user finishes typing
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            const searchValue = this.value.trim();

            // Clear previous timeout
            clearTimeout(searchTimeout);

            // Set new timeout - wait 600ms after user stops typing before searching
            searchTimeout = setTimeout(() => {
                // Reset to page 1 when searching
                const form = document.getElementById('searchForm');
                const existingPageInput = form.querySelector('input[name="page"]');

                if (existingPageInput) {
                    existingPageInput.value = '1';
                } else {
                    const pageInput = document.createElement('input');
                    pageInput.type = 'hidden';
                    pageInput.name = 'page';
                    pageInput.value = '1';
                    form.appendChild(pageInput);
                }

                form.submit();
            }, 600); // Wait 600ms after typing stops before searching
        });

        // Allow immediate search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                // Form will submit via default behavior
            }
        });
    }

    function initializeTableFunctionality() {
        initializeSorting();
        initializeFiltering();
        initializeActionHandlers();
        initializePaginationHandlers();
    }

    // Filter functionality
    function initializeFiltering() {
        // Filter dropdown items
        document.querySelectorAll('.filter-option').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filterValue = this.getAttribute('data-filter');
                applyFilter(filterValue);
            });
        });
    }

    function applyFilter(filterValue) {
        const currentUrl = new URL(window.location.href);
        const form = document.getElementById('searchForm');
        
        // Remove existing filter inputs
        const existingFilterInput = form.querySelector('input[name="filter"]');
        if (existingFilterInput) {
            existingFilterInput.remove();
        }
        
        // Add new filter
        if (filterValue && filterValue !== 'all') {
            const filterInput = document.createElement('input');
            filterInput.type = 'hidden';
            filterInput.name = 'filter';
            filterInput.value = filterValue;
            form.appendChild(filterInput);
        }
        
        // Reset to page 1 when filtering
        const existingPageInput = form.querySelector('input[name="page"]');
        if (existingPageInput) {
            existingPageInput.value = '1';
        } else {
            const pageInput = document.createElement('input');
            pageInput.type = 'hidden';
            pageInput.name = 'page';
            pageInput.value = '1';
            form.appendChild(pageInput);
        }
        
        form.submit();
    }

    // Enhanced sorting with server-side support
    function initializeSorting() {
        document.querySelectorAll('th[data-sort]').forEach(th => {
            th.style.cursor = 'pointer';
            
            // Remove existing event listeners by cloning and replacing
            const newTh = th.cloneNode(true);
            th.parentNode.replaceChild(newTh, th);
            
            // Add new event listener
            newTh.addEventListener('click', function() {
                const sortField = this.getAttribute('data-sort');
                const currentUrl = new URL(window.location.href);
                const currentSort = currentUrl.searchParams.get('sort');
                const currentDirection = currentUrl.searchParams.get('direction');
                
                let newDirection = 'asc';
                if (currentSort === sortField) {
                    newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                }
                
                // Update URL and submit form
                const form = document.getElementById('searchForm');
                
                // Update sort inputs
                const sortInput = form.querySelector('input[name="sort"]');
                const directionInput = form.querySelector('input[name="direction"]');
                
                if (sortInput) sortInput.value = sortField;
                if (directionInput) directionInput.value = newDirection;
                
                form.submit();
            });
            
            // Add visual indicators for current sort
            const currentUrl = new URL(window.location.href);
            const currentSort = currentUrl.searchParams.get('sort');
            const currentDirection = currentUrl.searchParams.get('direction');
            
            if (currentSort === newTh.getAttribute('data-sort')) {
                const icon = newTh.querySelector('i');
                if (icon) {
                    icon.className = currentDirection === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
                }
                newTh.classList.add(currentDirection === 'asc' ? 'sort-asc' : 'sort-desc');
            }
        });
    }

    // Initialize action handlers
    function initializeActionHandlers() {
        // Handle approve and delete buttons with confirmation
        document.addEventListener('click', function(e) {
            // Approve button
            if (e.target.classList.contains('approve-btn') || e.target.closest('.approve-btn')) {
                const button = e.target.classList.contains('approve-btn') ? e.target : e.target.closest('.approve-btn');
                if (confirm('Approve this user?')) {
                    button.closest('.approve-form').submit();
                }
            }
            
            // Delete button
            if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
                const button = e.target.classList.contains('delete-btn') ? e.target : e.target.closest('.delete-btn');
                if (confirm('Are you sure you want to DELETE this USER?')) {
                    button.closest('.delete-form').submit();
                }
            }
        });
    }

    // Pagination — single delegated listener instead of per-link
    function initializePaginationHandlers() {
        const paginationContainer = document.querySelector('.pagination');
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!link) return;
                e.preventDefault();
                window.location.href = link.getAttribute('href');
            });
        }
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        // Reload the page to maintain consistency
        window.location.reload();
    });

    // Initial initialization
    initializeTableFunctionality();
});

document.addEventListener('DOMContentLoaded', function() {
    // student ID change confirmation
    const studentInput = document.querySelector('input[name="student_id"]');
    if (studentInput) {
        const originalValue = studentInput.value;
        studentInput.addEventListener('change', function() {
            if (this.value !== originalValue) {
                if (!confirm('Are you sure you want to change the student ID?')) {
                    this.value = originalValue;
                }
            }
        });
    }

    // Role-based field visibility - WITH NULL CHECKS
    const roleSelect = document.getElementById('roleSelect');
    const studentFields = document.getElementById('studentFields');
    const instructorFields = document.getElementById('instructorFields');

    function toggleRoleFields() {
        // Check if roleSelect exists before accessing its value
        if (!roleSelect) return;
        
        const role = roleSelect.value;
        
        // Hide all role-dependent fields first
        document.querySelectorAll('.role-dependent').forEach(field => {
            field.style.display = 'none';
        });

        // Show relevant fields based on role
        if (role === 'student' && studentFields) {
            studentFields.style.display = 'block';
            initializeSectionField();
        } else if (role === 'instructor' && instructorFields) {
            instructorFields.style.display = 'block';
        }
    }

    // Section selection functionality - WITH NULL CHECKS
    function initializeSectionField() {
        const sectionSelect = document.getElementById('sectionSelect');
        if (!sectionSelect) return;

        // Clean up any options with blade syntax
        Array.from(sectionSelect.options).forEach(option => {
            if (option.value.includes('{{') || option.textContent.includes('{{')) {
                option.remove();
            }
        });

        // Only run PHP-related code if we're in a Blade context
        if (typeof currentSection !== 'undefined') {
            const currentSection = '<?php echo e(old("section", $user->section)); ?>';
            
            // If current section exists and is not in predefined options, add it
            if (currentSection && currentSection !== '' && 
                !['A1', 'B1', 'C1', 'D1', 'custom', ''].includes(currentSection)) {
                
                const existingOption = Array.from(sectionSelect.options).find(
                    option => option.value === currentSection
                );
                
                if (!existingOption) {
                    const newOption = document.createElement('option');
                    newOption.value = currentSection;
                    newOption.textContent = currentSection;
                    newOption.selected = true;
                    // Insert before the "custom" option
                    const customOption = sectionSelect.querySelector('option[value="custom"]');
                    if (customOption) {
                        sectionSelect.insertBefore(newOption, customOption);
                    } else {
                        sectionSelect.appendChild(newOption);
                    }
                }
            }
        }
    }

    // Handle section selection change - WITH NULL CHECKS
    const sectionSelect = document.getElementById('sectionSelect');
    if (sectionSelect) {
        sectionSelect.addEventListener('change', function() {
            const customSectionInput = document.getElementById('customSectionInput');
            if (!customSectionInput) return;
            
            if (this.value === 'custom') {
                customSectionInput.classList.remove('d-none');
                customSectionInput.required = true;
                this.required = false;
                customSectionInput.focus();
            } else {
                customSectionInput.classList.add('d-none');
                customSectionInput.required = false;
                this.required = true;
                customSectionInput.value = '';
            }
        });
    }

    // Handle custom section input - WITH NULL CHECKS
    const customSectionInput = document.getElementById('customSectionInput');
    if (customSectionInput) {
        customSectionInput.addEventListener('input', function() {
            const sectionSelect = document.getElementById('sectionSelect');
            if (sectionSelect && this.value.trim() !== '') {
                sectionSelect.value = this.value.trim();
            }
        });
    }

    // Form submission handler - WITH NULL CHECKS
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const sectionSelect = document.getElementById('sectionSelect');
            const customSectionInput = document.getElementById('customSectionInput');
            
            if (customSectionInput && !customSectionInput.classList.contains('d-none') && 
                customSectionInput.value.trim() !== '') {
                const customValue = customSectionInput.value.trim();
                if (sectionSelect) {
                    sectionSelect.value = customValue;
                }
            }
        });
    }

    // Initial toggle - ONLY if roleSelect exists
    if (roleSelect) {
        toggleRoleFields();
        roleSelect.addEventListener('change', toggleRoleFields);
    }

});
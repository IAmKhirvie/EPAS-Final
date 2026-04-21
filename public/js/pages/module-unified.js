document.addEventListener('DOMContentLoaded', function () {
    const moduleData = document.getElementById('moduleData');
    const csrfToken = moduleData.dataset.csrf;
    const baseUrl = moduleData.dataset.baseUrl;
    const moduleId = moduleData.dataset.moduleId;
    const moduleSlug = moduleData.dataset.moduleSlug;
    const courseId = moduleData.dataset.courseId;
    const userRole = document.body.dataset.userRole || 'student';
    const isStudent = userRole === 'student';

    // ==================== TOC NAVIGATION ====================

    // Dropdown toggle buttons - expand/collapse topics
    document.querySelectorAll('.sidebar-dropdown-toggle').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const sheetItem = this.closest('.sidebar-sheet-item');
            const wasExpanded = sheetItem.classList.contains('expanded');

            // Collapse all
            document.querySelectorAll('.sidebar-sheet-item').forEach(function (item) {
                item.classList.remove('expanded');
            });

            // Toggle this one
            if (!wasExpanded) {
                sheetItem.classList.add('expanded');
            }
        });
    });

    // Sheet headers - show "Start Reading" view (skip if locked)
    document.querySelectorAll('.sidebar-sheet-header').forEach(function (header) {
        header.addEventListener('click', function (e) {
            e.preventDefault();
            // Block locked sheets
            if (this.classList.contains('locked') || this.closest('.sheet-locked')) {
                return;
            }
            const sheetId = this.dataset.sheetId;
            const sheetIndex = parseInt(this.dataset.sheetIndex) || 0;
            showSheetStartReading(sheetId, sheetIndex, this);
        });
    });

    // Topic items - enter focus mode at specific topic (skip if in locked sheet)
    document.querySelectorAll('.sidebar-topic-item:not(a)').forEach(function (item) {
        item.addEventListener('click', function () {
            if (this.closest('.sheet-locked')) return;
            const topicId = this.dataset.topicId;
            const sheetId = this.dataset.sheetId;

            if (topicId) {
                // Find the index in focusModeData and enter focus mode
                enterFocusModeAtTopic(sheetId, topicId);
            }

            setActiveItem(this);
            closeMobileToc();
        });
    });

    // Overview link
    const overviewLink = document.querySelector('[data-section="overview"]');
    if (overviewLink) {
        overviewLink.addEventListener('click', function (e) {
            e.preventDefault();
            document.getElementById('overviewSection').style.display = 'block';
            document.getElementById('dynamicContent').style.display = 'none';
            setActiveItem(this);
            closeMobileToc();
        });
    }

    function setActiveItem(element) {
        document.querySelectorAll('.sidebar-toc-link').forEach(function (l) { l.classList.remove('active'); });
        document.querySelectorAll('.sidebar-topic-item').forEach(function (l) { l.classList.remove('active'); });
        document.querySelectorAll('.sidebar-sheet-header').forEach(function (l) { l.classList.remove('active'); });
        element.classList.add('active');
    }

    // ==================== SHEET "START READING" VIEW ====================

    function showSheetStartReading(sheetId, sheetIndex, headerElement) {
        const contentArea = document.getElementById('dynamicContent');
        const overviewSection = document.getElementById('overviewSection');

        overviewSection.style.display = 'none';
        contentArea.style.display = 'block';

        // Get sheet info from the header
        const sheetItem = headerElement.closest('.sidebar-sheet-item');
        const sheetTitle = sheetItem.querySelector('.sidebar-sheet-main').textContent;
        const sheetSub = sheetItem.querySelector('.sidebar-sheet-sub').textContent;

        // Count topics
        const topicItems = sheetItem.querySelectorAll('.sidebar-topic-item[data-topic-id]');
        const topicCount = topicItems.length;

        contentArea.innerHTML = `
            <div class="start-reading-card">
                <div class="start-reading-icon">
                    <i class="fas fa-book-reader"></i>
                </div>
                <h2 class="start-reading-title">${sheetTitle}</h2>
                <p class="start-reading-meta">${sheetSub}</p>
                <div class="start-reading-info">
                    <div class="info-item">
                        <i class="fas fa-file-alt"></i>
                        <span>${topicCount} Topics</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span>~${Math.ceil(topicCount * 3)} min read</span>
                    </div>
                </div>
                <button class="btn btn-category btn-lg start-reading-btn" data-sheet-id="${sheetId}" data-sheet-index="${sheetIndex}">
                    <i class="fas fa-play me-2"></i> Start Reading
                </button>
                <p class="start-reading-hint">
                    <i class="fas fa-info-circle me-1"></i>
                    Opens in Focus Mode for distraction-free reading
                </p>
            </div>
        `;

        // Bind the start reading button
        contentArea.querySelector('.start-reading-btn').addEventListener('click', function () {
            const sId = this.dataset.sheetId;
            enterFocusModeAtSheet(sId);
        });

        setActiveItem(headerElement);
    }

    // ==================== MOBILE TOC ====================

    const tocToggle = document.getElementById('tocMobileToggle');
    const sidebar = document.querySelector('.sidebar-section');

    if (tocToggle && sidebar) {
        const overlay = document.createElement('div');
        overlay.className = 'toc-mobile-overlay';
        document.body.appendChild(overlay);

        tocToggle.addEventListener('click', function () {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', closeMobileToc);
    }

    function closeMobileToc() {
        if (sidebar) sidebar.classList.remove('mobile-open');
        const overlay = document.querySelector('.toc-mobile-overlay');
        if (overlay) overlay.classList.remove('active');
    }

    // ==================== PROGRESS ====================

    function fetchProgress() {
        fetch(`/courses/${courseId}/module-${moduleSlug}/progress`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            updateProgressDisplay(data);
        })
        .catch(() => {});
    }

    fetchProgress();

    function updateProgressDisplay(progress) {
        const percentage = progress.percentage || 0;

        // Update progress circle
        const circle = document.getElementById('progressCircle');
        if (circle) {
            const circumference = 2 * Math.PI * 40;
            const offset = circumference - (percentage / 100) * circumference;
            circle.style.strokeDasharray = circumference;
            circle.style.strokeDashoffset = offset;
        }

        // Update progress text
        const progressText = document.getElementById('progressText');
        if (progressText) progressText.textContent = Math.round(percentage) + '%';

        // Update progress badge
        const progressBadge = document.getElementById('progressBadge');
        if (progressBadge) progressBadge.textContent = Math.round(percentage) + '%';

        // Update completed count (show "X of Y" format)
        const completedCount = document.getElementById('completedCount');
        if (completedCount) {
            const completed = progress.completed_items !== undefined ? progress.completed_items : (progress.completed || 0);
            const total = progress.total_items !== undefined ? progress.total_items : (progress.total || 0);
            completedCount.textContent = completed + ' of ' + total;
        }
    }

    // ==================== FOCUS MODE ====================

    const focusModeContainer = document.getElementById('focusModeContainer');
    const focusModeDataEl = document.getElementById('focusModeData');
    let focusModeData = [];
    let currentFocusIndex = 0;
    let currentImageIndex = 0;

    // Track completed sections
    let completedSections = new Set();
    const storageKey = 'focus_completed_' + moduleId;
    try {
        const saved = localStorage.getItem(storageKey);
        if (saved) completedSections = new Set(JSON.parse(saved));
    } catch (e) {}

    // Track if current section has been scrolled to bottom
    let currentSectionScrolled = false;
    let scrollHandler = null;

    try {
        focusModeData = JSON.parse(focusModeDataEl.textContent);
    } catch (e) {
        console.error('Failed to parse focus mode data:', e);
    }

    function enterFocusMode() {
        currentFocusIndex = 0;
        startFocusMode();
    }

    function enterFocusModeAtSheet(sheetId) {
        // Find first topic of this sheet
        for (let i = 0; i < focusModeData.length; i++) {
            if (focusModeData[i].sheetId == sheetId && focusModeData[i].type === 'topic') {
                currentFocusIndex = i;
                break;
            }
        }
        startFocusMode();
    }

    function enterFocusModeAtTopic(sheetId, topicId) {
        // Find this specific topic
        for (let i = 0; i < focusModeData.length; i++) {
            if (focusModeData[i].id == topicId && focusModeData[i].type === 'topic') {
                currentFocusIndex = i;
                break;
            }
        }
        startFocusMode();
    }

    function startFocusMode() {
        document.body.classList.add('focus-mode-active');
        focusModeContainer.classList.add('active');
        updateFocusContent();
        document.addEventListener('keydown', focusKeyHandler);
        setupScrollTracking();
    }

    function exitFocusMode() {
        document.body.classList.remove('focus-mode-active');
        focusModeContainer.classList.remove('active');
        document.removeEventListener('keydown', focusKeyHandler);
        removeScrollTracking();
        // Refresh progress when exiting
        fetchProgress();
    }

    function focusKeyHandler(e) {
        // Don't capture keys when typing in inputs/textareas
        const tag = e.target.tagName.toLowerCase();
        const isTyping = tag === 'input' || tag === 'textarea' || e.target.isContentEditable;

        if (e.key === 'Escape') exitFocusMode();
        else if (!isTyping && (e.key === 'ArrowRight' || e.key === ' ')) {
            e.preventDefault();
            if (canProceedToNext()) nextFocusContent();
        }
        else if (!isTyping && e.key === 'ArrowLeft') { e.preventDefault(); prevFocusContent(); }
        else if (!isTyping && e.key === 'ArrowUp') { e.preventDefault(); prevImage(); }
        else if (!isTyping && e.key === 'ArrowDown') { e.preventDefault(); nextImage(); }
    }

    function canProceedToNext() {
        return completedSections.has(currentFocusIndex) || currentSectionScrolled;
    }

    function setupScrollTracking() {
        removeScrollTracking();
        const contentPanel = document.querySelector('.focus-content-panel');
        if (!contentPanel) return;

        scrollHandler = function() {
            checkScrollCompletion(contentPanel);
        };
        contentPanel.addEventListener('scroll', scrollHandler);
    }

    function removeScrollTracking() {
        if (scrollHandler) {
            const contentPanel = document.querySelector('.focus-content-panel');
            if (contentPanel) {
                contentPanel.removeEventListener('scroll', scrollHandler);
            }
            scrollHandler = null;
        }
    }

    function checkScrollCompletion(panel) {
        const scrolledToBottom = panel.scrollHeight - panel.scrollTop - panel.clientHeight < 50;

        if (scrolledToBottom && !currentSectionScrolled) {
            currentSectionScrolled = true;
            markCurrentSectionComplete();
        }

        updateNextButtonState();
    }

    function markCurrentSectionComplete() {
        completedSections.add(currentFocusIndex);

        // Save to localStorage
        try {
            localStorage.setItem(storageKey, JSON.stringify([...completedSections]));
        } catch (e) {}

        // If this is a topic, call the API to record progress
        const content = focusModeData[currentFocusIndex];
        if (content && content.type === 'topic' && content.id && content.sheetId) {
            markTopicCompleteOnServer(content.sheetId, content.id);
        }

        updateNextButtonState();
    }

    function markTopicCompleteOnServer(sheetId, topicId) {
        // Topic completion tracked silently

        fetch(`${baseUrl}/sheets/${sheetId}/topics/${topicId}/complete`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            // Response received
            if (data.success && data.progress) {
                updateProgressDisplay(data.progress);
            }
        })
        .catch(err => console.error('Failed to mark topic complete:', err));
    }

    function updateNextButtonState() {
        const nextBtn = document.getElementById('focusNextBtn');
        if (!nextBtn) return;

        const content = focusModeData[currentFocusIndex];
        const isLastSection = currentFocusIndex >= focusModeData.length - 1;
        const isSelfCheck = content && content.type === 'self_check';
        const isActivity = content && ['task_sheet', 'job_sheet'].includes(content.type);
        const canProceed = canProceedToNext();

        // Always keep the arrow icon
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';

        // Remove or add status label
        let statusLabel = document.getElementById('focusStatusLabel');
        if (!statusLabel) {
            statusLabel = document.createElement('div');
            statusLabel.id = 'focusStatusLabel';
            statusLabel.className = 'focus-status-label';
            document.querySelector('.focus-mode-container').appendChild(statusLabel);
        }

        if (isSelfCheck) {
            if (!isStudent || selfCheckState.passed) {
                // Admin/instructor can always proceed, students only after passing
                nextBtn.classList.remove('disabled');
                statusLabel.textContent = '';
                statusLabel.style.display = 'none';
            } else if (selfCheckState.submitted && !selfCheckState.passed) {
                nextBtn.classList.add('disabled');
                statusLabel.textContent = 'Retry Required';
                statusLabel.style.display = 'block';
            } else {
                nextBtn.classList.add('disabled');
                statusLabel.textContent = 'Complete Self-Check First';
                statusLabel.style.display = 'block';
            }
        } else if (isActivity) {
            if (!isStudent) {
                nextBtn.classList.remove('disabled');
                statusLabel.textContent = '';
                statusLabel.style.display = 'none';
            } else {
                nextBtn.classList.add('disabled');
                statusLabel.textContent = 'Complete Activity First';
                statusLabel.style.display = 'block';
            }
        } else if (!isLastSection && !canProceed) {
            nextBtn.classList.add('disabled');
            statusLabel.textContent = '';
            statusLabel.style.display = 'none';
        } else {
            nextBtn.classList.remove('disabled');
            statusLabel.textContent = '';
            statusLabel.style.display = 'none';
        }
    }

    // Focus mode dot navigation — only shows dots for current sheet's content
    function updateFocusDots() {
        let dotsContainer = document.getElementById('focusDotsNav');
        if (!dotsContainer) {
            dotsContainer = document.createElement('div');
            dotsContainer.id = 'focusDotsNav';
            dotsContainer.className = 'focus-dots-nav';
            const container = document.querySelector('.focus-mode-container');
            if (container) container.appendChild(dotsContainer);
        }

        // Find current sheet ID
        const currentItem = focusModeData[currentFocusIndex];
        const currentSheetId = currentItem ? currentItem.sheetId : null;

        let html = '';
        focusModeData.forEach((item, i) => {
            // Only show dots for same sheet as current item
            if (currentSheetId && item.sheetId !== currentSheetId) return;

            const isCurrent = i === currentFocusIndex;
            const isCompleted = completedSections.has(i);
            const isSelfCheck = item.type === 'self_check';
            const isActivity = ['task_sheet', 'job_sheet'].includes(item.type);
            let cls = 'focus-dot';
            if (isCurrent) cls += ' current';
            if (isCompleted) cls += ' completed';
            if (isSelfCheck) cls += ' selfcheck';
            if (isActivity) cls += ' activity';
            html += `<span class="${cls}" onclick="window.focusGoTo(${i})" title="${item.title}"></span>`;
        });
        dotsContainer.innerHTML = html;
    }

    window.focusGoTo = function(idx) {
        if (idx >= 0 && idx < focusModeData.length) {
            currentFocusIndex = idx;
            updateFocusContent();
        }
    };

    // Track self-check state
    let selfCheckState = {
        active: false,
        started: false,
        currentQuestion: 0,
        answers: {},
        submitted: false,
        passed: false,
        results: null,
        shuffledQuestions: null
    };

    function resetSelfCheckState() {
        selfCheckState = {
            active: false,
            started: false,
            currentQuestion: 0,
            answers: {},
            submitted: false,
            passed: false,
            results: null,
            shuffledQuestions: null
        };
    }

    function updateFocusContent() {
        const content = focusModeData[currentFocusIndex];
        if (!content) return;

        // Check if this is an activity (self_check, task_sheet, job_sheet)
        const isActivity = ['self_check', 'task_sheet', 'job_sheet'].includes(content.type);
        const isSelfCheck = content.type === 'self_check';

        // Reset self-check state when moving to a new section
        if (!isSelfCheck || !selfCheckState.active) {
            resetSelfCheckState();
        }

        // Reset scroll state for new section (unless already completed)
        currentSectionScrolled = completedSections.has(currentFocusIndex) || isActivity;

        document.getElementById('focusModeTitle').textContent = content.title;
        document.getElementById('focusContentTitle').textContent = content.title;

        // Build content and extract tables
        let bodyHtml = '';
        let extractedTables = [];

        if (isSelfCheck && content.questions && content.questions.length > 0) {
            // Render self-check quiz inside focus mode
            selfCheckState.active = true;

            if (selfCheckState.submitted && selfCheckState.results) {
                // Show results
                bodyHtml = renderSelfCheckResults(content, selfCheckState.results);
            } else if (selfCheckState.started) {
                // Show quiz questions
                bodyHtml = renderSelfCheckQuiz(content);
            } else {
                // Show intro with start button
                bodyHtml = renderSelfCheckIntro(content);
            }
        } else if (isActivity) {
            // Show activity intro card for task/job sheets
            const iconColor = content.color || '#6d9773';
            const icon = content.icon || 'clipboard-check';

            bodyHtml = `
                <div class="activity-intro-card">
                    <div class="activity-intro-icon" style="background: ${iconColor}20; color: ${iconColor};">
                        <i class="fas fa-${icon}"></i>
                    </div>
                    <h3 class="activity-intro-title">${content.title}</h3>
                    <p class="activity-intro-sheet">From: ${content.sheetTitle || 'Information Sheet'}</p>
                    <p class="activity-intro-description">${content.description || ''}</p>
                    <a href="${content.url}" class="btn btn-lg activity-start-btn" style="background: ${iconColor}; border-color: ${iconColor}; color: white;">
                        <i class="fas fa-play me-2"></i>Start ${content.type === 'task_sheet' ? 'Task Sheet' : 'Job Sheet'}
                    </a>
                    <p class="activity-intro-hint">
                        <i class="fas fa-info-circle me-1"></i>
                        Click the button above to begin, or use the navigation to continue exploring.
                    </p>
                </div>
            `;
        } else if (content.type === 'sheet' || content.type === 'overview') {
            // Title slide — styled like a presentation title page
            const sheetNum = content.type === 'sheet' ? (content.title.match(/Info Sheet [\d.]+/) || [''])[0] : '';
            const sheetTitle = content.type === 'sheet' ? content.title.replace(/Info Sheet [\d.]+:\s*/, '') : content.title;
            const subtitle = content.content ? content.content.replace(/<[^>]*>/g, '').trim() : '';

            bodyHtml = '<div class="focus-title-slide">' +
                (sheetNum ? '<div class="focus-title-badge">' + sheetNum + '</div>' : '') +
                '<h1 class="focus-title-heading">' + sheetTitle + '</h1>' +
                (subtitle && subtitle !== sheetTitle ? '<p class="focus-title-subtitle">' + subtitle + '</p>' : '') +
                '<div class="focus-title-divider"></div>' +
                '<p class="focus-title-hint"><i class="fas fa-arrow-right me-2"></i>Press Next to begin</p>' +
                '</div>';
        } else {
            if (content.content) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = content.content;

                const tables = tempDiv.querySelectorAll('table');
                tables.forEach(function(table) {
                    extractedTables.push(table.outerHTML);
                    table.remove();
                });

                // Preserve HTML formatting - only add line breaks if no HTML tags present
                let contentHtml = tempDiv.innerHTML;
                if (!/<[a-z][\s\S]*>/i.test(contentHtml)) {
                    contentHtml = contentHtml.replace(/\n/g, '<br>');
                }
                bodyHtml += '<div class="mb-4 rich-content">' + contentHtml + '</div>';
            }

            if (content.parts && content.parts.length > 0) {
                content.parts.forEach(function (part, idx) {
                    // Don't render part image inline — it shows in the side panel
                    let explanationHtml = part.explanation || '';
                    if (!/<[a-z][\s\S]*>/i.test(explanationHtml)) {
                        explanationHtml = explanationHtml.replace(/\n/g, '<br>');
                    }
                    bodyHtml += '<div class="part-section focus-part">' +
                        '<h3 class="focus-part-title">' + (part.title || '') + '</h3>' +
                        '<div class="focus-part-explanation">' + explanationHtml + '</div>' +
                        '</div>';
                });
            }

            // No scroll marker needed — content auto-fits to viewport
        }

        document.getElementById('focusContentBody').innerHTML = bodyHtml || '<p class="text-muted">No content available for this section.</p>';

        // Auto-scale font to fit content in viewport without scrolling
        // Range: 1rem (min) to 2rem (max), default 1.5rem
        autoFitFontSize();

        // Scroll content panel to top and check if scrolling is needed
        const contentPanel = document.querySelector('.focus-content-panel');
        if (contentPanel) {
            contentPanel.scrollTop = 0;

            // Check if content fits without scrolling (or is an activity)
            setTimeout(() => {
                if (isActivity || contentPanel.scrollHeight <= contentPanel.clientHeight + 50) {
                    currentSectionScrolled = true;
                    if (!isActivity) markCurrentSectionComplete();
                }
                updateNextButtonState();
            }, 100);
        }

        currentImageIndex = 0;
        updateFocusImageAndTables(content, extractedTables);

        document.getElementById('focusProgressBadge').textContent = (currentFocusIndex + 1) + ' / ' + focusModeData.length;
        const prevNav = document.getElementById('focusPrevBtn');
        if (prevNav) prevNav.classList.toggle('disabled', currentFocusIndex === 0);
        updateNextButtonState();
        updateFocusDots();

        // Re-setup scroll tracking for new content
        if (!isActivity) {
            setupScrollTracking();
        }
    }

    // Re-fit on resize (device rotation, window resize)
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (focusModeContainer.classList.contains('active')) {
                autoFitFontSize();
            }
        }, 200);
    });

    function autoFitFontSize() {
        const panel = document.querySelector('.focus-content-panel');
        const body = document.getElementById('focusContentBody');
        if (!panel || !body) return;

        const maxRem = 2;
        const minRem = 1;
        const defaultRem = 1.5;
        let currentRem = defaultRem;

        // Get root font size for rem calculation
        const rootSize = parseFloat(getComputedStyle(document.documentElement).fontSize);

        body.style.fontSize = currentRem + 'rem';

        // Wait a frame for layout to settle
        requestAnimationFrame(function() {
            // If content overflows, shrink until it fits or hits minimum
            while (panel.scrollHeight > panel.clientHeight + 5 && currentRem > minRem) {
                currentRem -= 0.05;
                body.style.fontSize = currentRem + 'rem';
            }

            // If there's lots of free space and we're below max, grow
            if (panel.scrollHeight <= panel.clientHeight - 100 && currentRem < maxRem) {
                while (panel.scrollHeight <= panel.clientHeight - 50 && currentRem < maxRem) {
                    currentRem += 0.05;
                    body.style.fontSize = currentRem + 'rem';
                }
                // Step back one if we overflowed
                if (panel.scrollHeight > panel.clientHeight + 5) {
                    currentRem -= 0.05;
                    body.style.fontSize = currentRem + 'rem';
                }
            }

            // Mark section complete since everything fits on screen
            currentSectionScrolled = true;
            markCurrentSectionComplete();
            updateNextButtonState();
        });
    }

    function updateFocusImage(content) {
        updateFocusImageAndTables(content, []);
    }

    function updateFocusImageAndTables(content, extractedTables) {
        const images = content.images || [];
        const noImage = document.getElementById('focusNoImage');
        const focusImage = document.getElementById('focusImage');
        const imageCaption = document.getElementById('focusImageCaption');
        const imageNav = document.getElementById('imageNav');
        const imageCounter = document.getElementById('imageCounter');
        const focusModeBody = document.querySelector('.focus-mode-body');

        focusModeBody.classList.remove('no-images', 'has-tables', 'quiz-mode');

        // For self-checks, always hide image panel and use full width
        const isSelfCheck = content && content.type === 'self_check';
        if (isSelfCheck) {
            focusModeBody.classList.add('no-images', 'quiz-mode');
            noImage.style.display = 'none';
            focusImage.style.display = 'none';
            imageNav.style.display = 'none';
            imageCaption.textContent = '';
            imageCounter.textContent = '';
            return;
        }

        const hasImages = images.length > 0;
        const hasTables = extractedTables && extractedTables.length > 0;

        if (!hasImages && !hasTables) {
            focusModeBody.classList.add('no-images');
            noImage.style.display = 'none';
            focusImage.style.display = 'none';
            imageNav.style.display = 'none';
            imageCaption.textContent = '';
            imageCounter.textContent = '';
        } else if (hasTables && !hasImages) {
            focusModeBody.classList.add('has-tables');
            noImage.style.display = 'none';
            focusImage.style.display = 'none';
            imageNav.style.display = 'none';

            let tableHtml = '<div class="table-label"><i class="fas fa-table me-2"></i>Reference Table</div>';
            extractedTables.forEach(function(table) {
                tableHtml += '<div class="table-container">' + table + '</div>';
            });
            imageCaption.innerHTML = tableHtml;
            imageCounter.textContent = extractedTables.length > 1 ? extractedTables.length + ' tables' : '';
        } else if (hasImages) {
            noImage.style.display = 'none';
            focusImage.style.display = 'block';
            const img = images[currentImageIndex];
            focusImage.src = typeof img === 'string' ? img : (img.url || img);
            imageCaption.textContent = typeof img === 'object' ? (img.caption || '') : '';

            if (images.length > 1) {
                imageNav.style.display = 'flex';
                imageCounter.textContent = 'Image ' + (currentImageIndex + 1) + ' of ' + images.length;
            } else {
                imageNav.style.display = 'none';
                imageCounter.textContent = '';
            }
        }
    }

    function nextFocusContent() {
        const content = focusModeData[currentFocusIndex];
        const isSelfCheck = content && content.type === 'self_check';

        // For self-checks, only proceed if passed
        if (isSelfCheck && !selfCheckState.passed) {
            return; // Can't proceed until passed
        }

        const canProceedNow = isSelfCheck ? selfCheckState.passed : canProceedToNext();

        if (currentFocusIndex < focusModeData.length - 1 && canProceedNow) {
            currentFocusIndex++;
            resetSelfCheckState();
            updateFocusContent();
        } else if (currentFocusIndex >= focusModeData.length - 1 && canProceedNow) {
            // Last section completed - exit focus mode and go back to module
            showNotification('All sections completed! Great work!', 'success');
            exitFocusMode();

            // Scroll to overview
            document.getElementById('overviewSection').style.display = 'block';
            document.getElementById('dynamicContent').style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function prevFocusContent() {
        if (currentFocusIndex > 0) {
            currentFocusIndex--;
            updateFocusContent();
        }
    }

    function nextImage() {
        const content = focusModeData[currentFocusIndex];
        if (currentImageIndex < (content.images || []).length - 1) {
            currentImageIndex++;
            updateFocusImage(content);
        }
    }

    function prevImage() {
        if (currentImageIndex > 0) {
            currentImageIndex--;
            updateFocusImage(focusModeData[currentFocusIndex]);
        }
    }

    // Focus mode event listeners
    document.getElementById('enterFocusMode').addEventListener('click', enterFocusMode);
    document.getElementById('focusModeFloatingBtn').addEventListener('click', enterFocusMode);
    document.getElementById('exitFocusMode').addEventListener('click', exitFocusMode);
    document.getElementById('focusPrevBtn').addEventListener('click', prevFocusContent);
    document.getElementById('focusNextBtn').addEventListener('click', nextFocusContent);
    document.getElementById('prevImage').addEventListener('click', prevImage);
    document.getElementById('nextImage').addEventListener('click', nextImage);

    // ==================== SELF-CHECK IN FOCUS MODE ====================

    function renderSelfCheckIntro(content) {
        const iconColor = content.color || '#ffc107';
        return `
            <div class="activity-intro-card">
                <div class="activity-intro-icon" style="background: ${iconColor}20; color: ${iconColor};">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h3 class="activity-intro-title">${content.title}</h3>
                <p class="activity-intro-sheet">From: ${content.sheetTitle || 'Information Sheet'}</p>
                <p class="activity-intro-description">${content.description || ''}</p>
                <div class="activity-intro-meta"><i class="fas fa-question-circle me-1"></i>${content.questionCount} Questions</div>
                <div class="activity-intro-meta"><i class="fas fa-chart-line me-1"></i>${content.passingScore}% Passing Score</div>
                <button onclick="window.startSelfCheckInFocus()" class="btn btn-lg activity-start-btn" style="background: ${iconColor}; border-color: ${iconColor}; color: #000;">
                    <i class="fas fa-play me-2"></i>Start Self-Check
                </button>
                <p class="activity-intro-hint">
                    <i class="fas fa-info-circle me-1"></i>
                    Answer all questions to test your understanding
                </p>
            </div>
        `;
    }

    function renderSelfCheckQuiz(content) {
        let questions = content.questions || [];

        // Always shuffle questions and store the shuffled order
        questions = shuffleArray([...questions]);
        selfCheckState.shuffledQuestions = questions;

        // Build dot navigation
        let dots = '';
        questions.forEach((q, idx) => {
            const answered = selfCheckState.answers && selfCheckState.answers[q.id] !== undefined;
            dots += `<span class="selfcheck-dot ${idx === 0 ? 'current' : ''} ${answered ? 'answered' : ''}" onclick="window.goToQuestion(${idx})" title="Question ${idx+1}"></span>`;
        });

        let html = `
            <div class="selfcheck-quiz-container">
                <div class="selfcheck-quiz-header">
                    <h4><i class="fas fa-clipboard-check me-2"></i>${content.title}</h4>
                    <div class="selfcheck-progress">
                        <span class="selfcheck-progress-text">Question <span id="currentQNum">1</span> of ${questions.length}</span>
                        <div class="selfcheck-progress-bar">
                            <div class="selfcheck-progress-fill" id="quizProgressFill" style="width: ${(1/questions.length)*100}%"></div>
                        </div>
                    </div>
                    <div class="selfcheck-dots" id="quizDots">${dots}</div>
                </div>
                <div class="selfcheck-questions">
        `;

        questions.forEach((q, idx) => {
            html += renderQuestion(q, idx, content.randomizeOptions);
        });

        html += `
                </div>
                <div class="selfcheck-quiz-footer">
                    <button type="button" class="btn btn-success d-none" id="submitQuizBtn" onclick="window.submitSelfCheck()">
                        <i class="fas fa-check me-1"></i>Submit Answers
                    </button>
                </div>
            </div>
            <div class="selfcheck-side-nav prev disabled" id="quizPrevNav" onclick="window.prevQuestion()">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="selfcheck-side-nav next" id="quizNextNav" onclick="window.nextQuestion()">
                <i class="fas fa-chevron-right"></i>
            </div>
        `;

        return html;
    }

    function renderQuestion(q, idx, randomizeOptions) {
        let options = q.options || [];
        // Always shuffle options for MC/MS
        if (Array.isArray(options) && ['multiple_choice', 'multiple_select'].includes(q.type)) {
            options = shuffleArray([...options]);
        }

        let html = `<div class="selfcheck-question ${idx === 0 ? 'active' : ''}" data-question-index="${idx}" data-question-id="${q.id}">`;
        html += `<div class="selfcheck-question-number">Question ${idx + 1}</div>`;
        html += `<div class="selfcheck-question-text">${q.text}</div>`;

        if (q.image) {
            html += `<div class="selfcheck-question-media"><img src="${q.image}" alt="Question image"></div>`;
        }
        if (q.audio) {
            html += `<div class="selfcheck-question-media"><audio controls src="${q.audio}"></audio></div>`;
        }
        if (q.video) {
            html += `<div class="selfcheck-question-media"><iframe src="${q.video}" allowfullscreen></iframe></div>`;
        }

        html += `<div class="selfcheck-options">`;

        switch(q.type) {
            case 'multiple_choice':
            case 'image_choice':
                options.forEach((opt, optIdx) => {
                    const optLabel = typeof opt === 'object' ? (opt.label || opt.text || opt) : opt;
                    const optImage = typeof opt === 'object' ? opt.image : null;
                    html += `
                        <label class="selfcheck-option">
                            <input type="radio" name="q_${q.id}" value="${optIdx}" onchange="window.saveAnswer(${q.id}, ${optIdx})">
                            <span class="selfcheck-option-marker">${String.fromCharCode(65 + optIdx)}</span>
                            ${optImage ? `<img src="${optImage}" class="selfcheck-option-image">` : ''}
                            <span class="selfcheck-option-text">${optLabel}</span>
                        </label>
                    `;
                });
                break;

            case 'multiple_select':
                options.forEach((opt, optIdx) => {
                    const optLabel = typeof opt === 'object' ? (opt.label || opt.text || opt) : opt;
                    html += `
                        <label class="selfcheck-option">
                            <input type="checkbox" name="q_${q.id}" value="${optIdx}" onchange="window.saveMultiAnswer(${q.id})">
                            <span class="selfcheck-option-marker">${String.fromCharCode(65 + optIdx)}</span>
                            <span class="selfcheck-option-text">${optLabel}</span>
                        </label>
                    `;
                });
                break;

            case 'true_false':
                html += `
                    <label class="selfcheck-option">
                        <input type="radio" name="q_${q.id}" value="true" onchange="window.saveAnswer(${q.id}, 'true')">
                        <span class="selfcheck-option-marker">T</span>
                        <span class="selfcheck-option-text">True</span>
                    </label>
                    <label class="selfcheck-option">
                        <input type="radio" name="q_${q.id}" value="false" onchange="window.saveAnswer(${q.id}, 'false')">
                        <span class="selfcheck-option-marker">F</span>
                        <span class="selfcheck-option-text">False</span>
                    </label>
                `;
                break;

            case 'numeric':
            case 'slider':
                const min = q.options?.min || 0;
                const max = q.options?.max || 100;
                const step = q.options?.step || 1;
                const unit = q.options?.unit || '';
                html += `
                    <div class="selfcheck-numeric-input">
                        <input type="number" class="form-control" min="${min}" max="${max}" step="${step}"
                               placeholder="Enter a number"
                               onchange="window.saveAnswer(${q.id}, this.value)">
                        ${unit ? `<span class="selfcheck-unit">${unit}</span>` : ''}
                    </div>
                `;
                break;

            case 'identification':
            case 'fill_blank':
            case 'short_answer':
                html += `
                    <input type="text" class="form-control selfcheck-text-input"
                           placeholder="Type your answer here..."
                           onchange="window.saveAnswer(${q.id}, this.value)"
                           oninput="window.saveAnswer(${q.id}, this.value)">
                `;
                break;

            case 'enumeration':
                html += `
                    <textarea class="form-control selfcheck-textarea" rows="4"
                              placeholder="List your answers, one per line..."
                              onchange="window.saveAnswer(${q.id}, this.value)"
                              oninput="window.saveAnswer(${q.id}, this.value)"></textarea>
                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i>Separate each answer with a new line</small>
                `;
                break;

            case 'essay':
                html += `
                    <textarea class="form-control selfcheck-textarea" rows="5"
                              placeholder="Write your answer here..."
                              onchange="window.saveAnswer(${q.id}, this.value)"
                              oninput="window.saveAnswer(${q.id}, this.value)"></textarea>
                `;
                break;

            default:
                html += `
                    <input type="text" class="form-control selfcheck-text-input"
                           placeholder="Type your answer here..."
                           onchange="window.saveAnswer(${q.id}, this.value)"
                           oninput="window.saveAnswer(${q.id}, this.value)">
                `;
        }

        html += `</div></div>`;
        return html;
    }

    function renderSelfCheckResults(content, results) {
        const passed = results.passed || false;
        const percentage = Number(results.percentage) || 0;
        const score = Number(results.score) || 0;
        const total = Number(results.total) || 0;
        const details = results.details || [];
        const questions = content.questions || [];

        let html = `
            <div class="selfcheck-results">
                <div class="selfcheck-results-header ${passed ? 'passed' : 'failed'}">
                    <i class="fas ${passed ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                    <h3>${passed ? 'Congratulations!' : 'Keep Trying!'}</h3>
                    <p>${passed ? 'You passed the self-check!' : "You didn't pass this time."}</p>
                </div>
                <div class="selfcheck-results-score">
                    <div class="score-circle ${passed ? 'passed' : 'failed'}">
                        <span class="score-value">${percentage.toFixed(1)}%</span>
                    </div>
                    <div class="score-details">
                        <p><strong>${score}</strong> out of <strong>${total}</strong> points</p>
                        <p class="text-muted">Passing score: ${content.passingScore}%</p>
                    </div>
                </div>

                <div class="selfcheck-results-actions" style="margin-bottom:1.5rem;">
                    ${passed ? `
                        <button class="btn btn-success" onclick="window.continueFocusMode()">
                            <i class="fas fa-arrow-right me-2"></i>Continue
                        </button>
                    ` : `
                        <button class="btn btn-primary" onclick="window.retrySelfCheck()">
                            <i class="fas fa-redo me-2"></i>Try Again
                        </button>
                    `}
                </div>

                <div class="selfcheck-review">
                    <h5 style="margin-bottom:1rem;font-weight:700;"><i class="fas fa-list-check me-2"></i>Answer Review</h5>
        `;

        questions.forEach((q, idx) => {
            const detail = details.find(d => d.question_id === q.id) || {};
            const isCorrect = detail.is_correct || false;
            const userAnswer = selfCheckState.answers[q.id];
            let userAnswerText = '';
            let correctAnswerText = q.correct_answer || '';

            // Resolve user answer to readable text
            if (q.type === 'multiple_choice' || q.type === 'image_choice') {
                const opts = q.options || [];
                if (userAnswer !== undefined && userAnswer !== null && opts[userAnswer]) {
                    const opt = opts[userAnswer];
                    userAnswerText = typeof opt === 'object' ? (opt.label || opt.text || opt) : opt;
                } else {
                    userAnswerText = userAnswer ?? 'No answer';
                }
            } else if (q.type === 'true_false') {
                userAnswerText = userAnswer === 'true' ? 'True' : userAnswer === 'false' ? 'False' : 'No answer';
            } else {
                userAnswerText = userAnswer || 'No answer';
            }

            html += `
                <div class="review-item" style="padding:0.75rem 1rem;margin-bottom:0.5rem;border-radius:8px;border-left:4px solid ${isCorrect ? '#198754' : '#dc3545'};background:${isCorrect ? 'rgba(25,135,84,0.05)' : 'rgba(220,53,69,0.05)'};">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:0.25rem;">
                        <strong style="font-size:0.85rem;">Q${idx + 1}. ${q.text}</strong>
                        <span style="flex-shrink:0;margin-left:0.5rem;">
                            ${isCorrect
                                ? '<i class="fas fa-check-circle" style="color:#198754;"></i>'
                                : '<i class="fas fa-times-circle" style="color:#dc3545;"></i>'}
                            <small style="color:var(--text-muted);margin-left:0.25rem;">${detail.points_earned || 0}/${q.points || 1}</small>
                        </span>
                    </div>
                    <div style="font-size:0.8rem;">
                        <div style="color:${isCorrect ? '#198754' : '#dc3545'};"><strong>Your answer:</strong> ${userAnswerText}</div>
                        ${!isCorrect ? `<div style="color:#198754;"><strong>Correct:</strong> ${correctAnswerText}</div>` : ''}
                    </div>
                </div>
            `;
        });

        html += `</div></div>`;
        return html;
    }

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    // Global functions for quiz interaction
    window.startSelfCheckInFocus = function() {
        selfCheckState.started = true;
        selfCheckState.currentQuestion = 0;
        selfCheckState.answers = {};
        updateFocusContent();

        // Enter key on single-line inputs goes to next question
        setTimeout(() => {
            document.querySelectorAll('.selfcheck-text-input, .selfcheck-numeric-input input').forEach(input => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        window.nextQuestion();
                    }
                });
            });
        }, 100);
    };

    window.saveAnswer = function(questionId, value) {
        if (value !== undefined && value !== null && String(value).trim() !== '') {
            selfCheckState.answers[questionId] = value;
        } else {
            delete selfCheckState.answers[questionId];
        }
        updateQuizProgress();
        updateQuizNavButtons();
    };

    window.saveMultiAnswer = function(questionId) {
        const checkboxes = document.querySelectorAll(`input[name="q_${questionId}"]:checked`);
        const values = Array.from(checkboxes).map(cb => parseInt(cb.value));
        selfCheckState.answers[questionId] = values;
        updateQuizProgress();
    };

    window.nextQuestion = function() {
        const questions = selfCheckState.shuffledQuestions || (focusModeData[currentFocusIndex] || {}).questions || [];
        const allQuestions = document.querySelectorAll('.selfcheck-question');
        const currentQ = questions[selfCheckState.currentQuestion];

        // Block next if current question not answered
        if (currentQ && selfCheckState.answers[currentQ.id] === undefined) {
            const activeCard = allQuestions[selfCheckState.currentQuestion];
            if (activeCard) {
                activeCard.style.outline = '2px solid #dc3545';
                activeCard.querySelector('.selfcheck-question-text').style.color = '#dc3545';
                setTimeout(() => {
                    activeCard.style.outline = '';
                    activeCard.querySelector('.selfcheck-question-text').style.color = '';
                }, 1500);
            }
            return;
        }

        if (selfCheckState.currentQuestion < questions.length - 1) {
            allQuestions[selfCheckState.currentQuestion].classList.remove('active');
            selfCheckState.currentQuestion++;
            allQuestions[selfCheckState.currentQuestion].classList.add('active');
            updateQuizNavButtons();
            updateQuizProgress();
        }
    };

    window.prevQuestion = function() {
        const allQuestions = document.querySelectorAll('.selfcheck-question');

        if (selfCheckState.currentQuestion > 0) {
            allQuestions[selfCheckState.currentQuestion].classList.remove('active');
            selfCheckState.currentQuestion--;
            allQuestions[selfCheckState.currentQuestion].classList.add('active');
            updateQuizNavButtons();
            updateQuizProgress();
        }
    };

    window.goToQuestion = function(idx) {
        const allQuestions = document.querySelectorAll('.selfcheck-question');
        if (idx >= 0 && idx < allQuestions.length) {
            allQuestions[selfCheckState.currentQuestion].classList.remove('active');
            selfCheckState.currentQuestion = idx;
            allQuestions[idx].classList.add('active');
            updateQuizNavButtons();
            updateQuizProgress();
        }
    };

    function updateQuizNavButtons() {
        const questions = selfCheckState.shuffledQuestions || (focusModeData[currentFocusIndex] || {}).questions || [];
        const isFirst = selfCheckState.currentQuestion === 0;
        const isLast = selfCheckState.currentQuestion >= questions.length - 1;

        const submitBtn = document.getElementById('submitQuizBtn');
        const prevNav = document.getElementById('quizPrevNav');
        const nextNav = document.getElementById('quizNextNav');

        // Only show submit when ALL questions are answered
        if (submitBtn) {
            const questions = selfCheckState.shuffledQuestions || (focusModeData[currentFocusIndex] || {}).questions || [];
            const allAnswered = questions.length > 0 && questions.every(q =>
                selfCheckState.answers[q.id] !== undefined ||
                selfCheckState.answers[String(q.id)] !== undefined
            );

            if (allAnswered) {
                submitBtn.classList.remove('d-none');
                submitBtn.className = 'btn btn-success';
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Submit Answers';
                submitBtn.disabled = false;
            } else {
                submitBtn.classList.add('d-none');
            }
        }

        // Side nav arrows
        if (prevNav) {
            prevNav.classList.toggle('disabled', isFirst);
        }
        if (nextNav) {
            nextNav.classList.toggle('disabled', isLast);
        }
    }

    function updateQuizProgress() {
        // Use shuffled order so dots match displayed questions
        const questions = selfCheckState.shuffledQuestions || (focusModeData[currentFocusIndex] || {}).questions || [];

        const currentNum = document.getElementById('currentQNum');
        const progressFill = document.getElementById('quizProgressFill');

        const answeredCount = Object.keys(selfCheckState.answers).length;
        if (currentNum) currentNum.textContent = selfCheckState.currentQuestion + 1;
        if (progressFill && questions.length) progressFill.style.width = (answeredCount / questions.length * 100) + '%';

        // Update dots — follows shuffled order
        const dots = document.querySelectorAll('.selfcheck-dot');
        dots.forEach((dot, i) => {
            dot.classList.remove('current');
            if (i === selfCheckState.currentQuestion) dot.classList.add('current');
            const q = questions[i];
            if (q) {
                const isAnswered = selfCheckState.answers[q.id] !== undefined
                    || selfCheckState.answers[String(q.id)] !== undefined;
                dot.classList.toggle('answered', isAnswered);
            }
        });
    }

    window.submitSelfCheck = function() {
        const content = focusModeData[currentFocusIndex];

        // Build answers payload
        const answersPayload = {};
        Object.keys(selfCheckState.answers).forEach(qId => {
            answersPayload[qId] = selfCheckState.answers[qId];
        });

        // Show loading state
        const submitBtn = document.getElementById('submitQuizBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
        }

        // Submit via AJAX
        fetch(content.submitUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ answers: answersPayload, focus_mode: true })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Server error'); });
            }
            return response.json();
        })
        .then(data => {
            selfCheckState.submitted = true;
            selfCheckState.results = {
                passed: data.passed || false,
                percentage: data.percentage || 0,
                score: data.score || 0,
                total: data.total_points || 0,
                details: data.results || []
            };
            selfCheckState.passed = data.passed || false;

            // Mark as completed if passed
            if (data.passed) {
                completedSections.add(currentFocusIndex);
                try {
                    localStorage.setItem(storageKey, JSON.stringify([...completedSections]));
                } catch (e) {}
            }

            updateFocusContent();
            fetchProgress();
        })
        .catch(error => {
            console.error('Submit error:', error);
            showNotification('Failed to submit. Please try again.', 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Submit Answers';
            }
        });
    };

    window.retrySelfCheck = function() {
        resetSelfCheckState();
        selfCheckState.active = true;
        updateFocusContent();
    };

    window.reviewSelfCheck = function() {
        // For now, just go to the full self-check page
        const content = focusModeData[currentFocusIndex];
        if (content.url) {
            window.location.href = content.url;
        }
    };

    window.continueFocusMode = function() {
        // Move to next section
        if (currentFocusIndex < focusModeData.length - 1) {
            currentFocusIndex++;
            resetSelfCheckState();
            updateFocusContent();
        } else {
            showNotification('All sections completed!', 'success');
            exitFocusMode();
        }
    };

    // ==================== OFFLINE SAVE ====================

    const saveOfflineBtn = document.getElementById('saveOfflineBtn');

    saveOfflineBtn.addEventListener('click', function () {
        saveOfflineBtn.disabled = true;
        saveOfflineBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';

        try {
            const moduleTitle = document.querySelector('.module-header-section h4')?.textContent || 'Module';
            const offlineHtml = generateOfflineFocusModeHtml(focusModeData, moduleTitle);

            const blob = new Blob([offlineHtml], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = moduleTitle.replace(/[^a-zA-Z0-9]/g, '-').toLowerCase() + '-offline.html';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            saveOfflineBtn.innerHTML = '<i class="fas fa-check me-1"></i> Downloaded';
            saveOfflineBtn.classList.remove('btn-outline-warning');
            saveOfflineBtn.classList.add('btn-success');
            showNotification('Focus mode version downloaded!', 'success');
        } catch (error) {
            console.error('Offline save error:', error);
            saveOfflineBtn.innerHTML = '<i class="fas fa-cloud-download-alt me-1"></i> Save Offline';
            showNotification('Failed to generate offline version.', 'error');
        } finally {
            saveOfflineBtn.disabled = false;
        }
    });

    function generateOfflineFocusModeHtml(data, title) {
        if (!data || !Array.isArray(data) || data.length === 0) {
            return '<!DOCTYPE html><html><head><title>No Content</title></head><body><h1>No content available</h1></body></html>';
        }

        function safeText(text) {
            if (!text) return '';
            return String(text);
        }

        let contentHtml = '';
        data.forEach(function(item, index) {
            if (!item) return;

            let itemContent = '';
            if (item.content) {
                itemContent += '<div class="content-text">' + safeText(item.content).replace(/\n/g, '<br>') + '</div>';
            }
            if (item.parts && Array.isArray(item.parts) && item.parts.length > 0) {
                item.parts.forEach(function(part, pIdx) {
                    if (!part) return;
                    itemContent += '<div class="part-section">' +
                        '<h4><span class="part-badge">' + (pIdx + 1) + '</span> ' + safeText(part.title) + '</h4>' +
                        '<p>' + safeText(part.explanation).replace(/\n/g, '<br>') + '</p>' +
                        '</div>';
                });
            }

            contentHtml += '<section class="content-section" id="section-' + index + '">' +
                '<h2>' + safeText(item.title || 'Section ' + (index + 1)) + '</h2>' +
                itemContent +
                '</section>';
        });

        let navHtml = '<nav class="offline-nav"><ul>';
        data.forEach(function(item, index) {
            if (!item) return;
            navHtml += '<li><a href="#section-' + index + '">' + safeText(item.title || 'Section ' + (index + 1)) + '</a></li>';
        });
        navHtml += '</ul></nav>';

        const isDarkMode = document.body.classList.contains('dark-mode');

        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">' +
            '<title>' + title + ' - Offline</title><style>' +
            'body{font-family:-apple-system,sans-serif;margin:0;' + (isDarkMode ? 'background:#121220;color:#e9ecef;' : 'background:#f8f9fa;color:#333;') + '}' +
            '.header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:20px 30px;position:sticky;top:0;z-index:100}' +
            '.header h1{margin:0;font-size:1.5rem}.container{display:flex;max-width:1400px;margin:0 auto}' +
            '.offline-nav{width:280px;padding:20px;position:sticky;top:80px;height:calc(100vh - 80px);overflow-y:auto;' + (isDarkMode ? 'background:#1a1a2e;' : 'background:#fff;') + 'border-right:1px solid ' + (isDarkMode ? '#3a3a5a' : '#dee2e6') + '}' +
            '.offline-nav ul{list-style:none;padding:0;margin:0}.offline-nav li{margin-bottom:8px}' +
            '.offline-nav a{display:block;padding:10px 15px;text-decoration:none;border-radius:8px;' + (isDarkMode ? 'color:#adb5bd;background:#2a2a3e;' : 'color:#333;background:#f8f9fa;') + '}' +
            '.offline-nav a:hover{background:#667eea;color:#fff}.main-content{flex:1;padding:30px 50px;max-width:900px}' +
            '.content-section{margin-bottom:60px;padding-bottom:40px;border-bottom:2px solid ' + (isDarkMode ? '#3a3a5a' : '#e9ecef') + '}' +
            '.content-section h2{color:#667eea;border-bottom:3px solid #667eea;padding-bottom:15px;margin-bottom:25px}' +
            '.part-section{padding:20px;margin:20px 0;border-radius:8px;border-left:4px solid #667eea;' + (isDarkMode ? 'background:#2a2a3e;' : 'background:#f8f9fa;') + '}' +
            '.part-badge{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;background:#667eea;color:#fff;border-radius:50%;font-size:.85rem;margin-right:10px}' +
            '@media(max-width:768px){.container{flex-direction:column}.offline-nav{width:100%;position:static;height:auto}.main-content{padding:20px}}' +
            '</style></head><body><header class="header"><h1>' + title + '</h1></header>' +
            '<div class="container">' + navHtml + '<main class="main-content">' + contentHtml +
            '<div style="text-align:center;padding:40px;color:#888"><p>— End of Module —</p></div></main></div></body></html>';
    }

    // ==================== NOTIFICATIONS ====================

    function showNotification(message, type) {
        const toast = document.createElement('div');
        toast.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' position-fixed';
        toast.style.cssText = 'bottom: 20px; right: 20px; z-index: 9999; animation: fadeIn 0.3s;';
        toast.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' + message;
        document.body.appendChild(toast);

        setTimeout(function () {
            toast.style.animation = 'fadeOut 0.3s';
            setTimeout(function () { toast.remove(); }, 300);
        }, 3000);
    }
});

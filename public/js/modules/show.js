// public/js/modules/show.js
document.addEventListener('DOMContentLoaded', function() {
    const dynamicContent = document.getElementById('dynamic-content');
    const tocItems = document.querySelectorAll('.toc-item');
    const informationSheetItems = document.querySelectorAll('.information-sheet-item');
    const topicItems = document.querySelectorAll('.topic-item');
    const startLearningBtn = document.querySelector('.start-learning-btn');
    const mobileTocToggle = document.getElementById('mobileTocToggle');
    const moduleTocSidebar = document.getElementById('moduleTocSidebar');
    const prevBtn = document.getElementById('sidebar-prev');
    const nextBtn = document.getElementById('sidebar-next');

    let currentSection = 'overview';
    let currentSheetId = null;
    let currentTopicId = null;

    // Initialize the LMS
    function initLMS() {
        setupEventListeners();
        updateProgress();
        // Overview is already loaded by default
    }

    function setupEventListeners() {
        // TOC item clicks
        tocItems.forEach(item => {
            const tocLink = item.querySelector('.toc-link');
            if (tocLink) {
                tocLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const contentType = this.getAttribute('data-content');
                    const sheetId = this.getAttribute('data-sheet-id');
                    
                    if (contentType === 'overview') {
                        loadOverview();
                        collapseAllSheets();
                        setActiveTocItem(this);
                        currentSection = 'overview';
                        currentSheetId = null;
                        currentTopicId = null;
                        updateNavigationButtons();
                    } else if (sheetId) {
                        toggleInformationSheet(item);
                        setActiveTocItem(this);
                        currentSheetId = sheetId;
                        currentTopicId = null;
                        updateNavigationButtons();
                    }
                });
            }
        });

        // Topic item clicks
        topicItems.forEach(item => {
            item.addEventListener('click', function() {
                const topicId = this.getAttribute('data-topic-id');
                const contentType = this.getAttribute('data-content-type');
                
                if (topicId) {
                    loadTopic(topicId);
                    setActiveTopicItem(this);
                    currentSection = 'topic';
                    currentTopicId = topicId;
                    updateNavigationButtons();
                } else if (contentType) {
                    loadContent(contentType);
                    setActiveTopicItem(this);
                    currentSection = 'content';
                    currentTopicId = contentType;
                    updateNavigationButtons();
                }
            });
        });

        // Start learning button
        if (startLearningBtn) {
            startLearningBtn.addEventListener('click', function() {
                const sheetId = this.getAttribute('data-sheet-id');
                const firstSheet = document.querySelector(`[data-sheet-id="${sheetId}"]`);
                if (firstSheet) {
                    const parentItem = firstSheet.closest('.information-sheet-item');
                    toggleInformationSheet(parentItem, true);
                    
                    // Load first topic
                    const firstTopic = parentItem.querySelector('.topic-item');
                    if (firstTopic) {
                        firstTopic.click();
                    }
                }
            });
        }

        // Mobile TOC toggle
        if (mobileTocToggle && moduleTocSidebar) {
            mobileTocToggle.addEventListener('click', function() {
                moduleTocSidebar.classList.toggle('mobile-visible');
            });
        }

        // Navigation buttons (disabled — not yet implemented)
        if (prevBtn) prevBtn.disabled = true;
        if (nextBtn) nextBtn.disabled = true;
    }

    function loadOverview() {
        // Overview content is already in the page, just show it
        const overviewHTML = document.getElementById('overview').innerHTML;
        dynamicContent.innerHTML = `
            <section class="content-section section-transition active-section">
                ${overviewHTML}
            </section>
        `;
        updateCurrentSectionDisplay('Module Overview');
        updateProgress();
    }

    function loadTopic(topicId) {
        const moduleId = document.getElementById('module-data').getAttribute('data-module-id');
        
        dynamicContent.classList.add('content-loading');
        
        fetch(`/topics/${topicId}/content`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            dynamicContent.innerHTML = `
                <section class="content-section section-transition">
                    ${data.html}
                </section>
            `;
            
            dynamicContent.classList.remove('content-loading');
            updateCurrentSectionDisplay('Topic Content');
            updateProgress();
            
            // Add animation
            const newSection = dynamicContent.querySelector('.content-section');
            setTimeout(() => {
                newSection.classList.add('content-loaded');
            }, 50);
        })
        .catch(error => {
            console.error('Error loading topic:', error);
            dynamicContent.innerHTML = `
                <div class="alert alert-danger">
                    Error loading content: ${error.message}. Please try again.
                </div>
            `;
            dynamicContent.classList.remove('content-loading');
        });
    }

    function loadContent(contentType) {
        const moduleId = document.getElementById('module-data').getAttribute('data-module-id');
        
        dynamicContent.classList.add('content-loading');
        
        fetch(`/module-content/${moduleId}/${contentType}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            dynamicContent.innerHTML = `
                <section class="content-section section-transition">
                    ${data.html}
                </section>
            `;
            
            dynamicContent.classList.remove('content-loading');
            updateCurrentSectionDisplay(getContentTitle(contentType));
            updateProgress();
            
            // Add animation
            const newSection = dynamicContent.querySelector('.content-section');
            setTimeout(() => {
                newSection.classList.add('content-loaded');
            }, 50);
        })
        .catch(error => {
            console.error('Error loading content:', error);
            dynamicContent.innerHTML = `
                <div class="alert alert-danger">
                    Error loading content: ${error.message}. Please try again.
                </div>
            `;
            dynamicContent.classList.remove('content-loading');
        });
    }

    function getContentTitle(contentType) {
        const titles = {
            'introduction': 'Introduction to Electronics and Electricity',
            'electric-history': 'Electric History',
            'static-electricity': 'Static Electricity',
            'free-electrons': 'Free Electrons, Introduction to Sources of Electricity',
            'alternative-energy': 'Alternative Energy',
            'electric-energy': 'Types of Electric Energy and Current',
            'materials': 'Types of Materials',
            'self-check': 'Self Check'
        };
        return titles[contentType] || contentType;
    }

    function toggleInformationSheet(item, forceExpand = false) {
        const isExpanded = item.classList.contains('expanded');
        
        // Collapse all other information sheets
        if (!isExpanded || forceExpand) {
            collapseAllSheets();
        }
        
        // Toggle current item
        if (!isExpanded || forceExpand) {
            item.classList.add('expanded');
        } else {
            item.classList.remove('expanded');
        }
    }

    function collapseAllSheets() {
        informationSheetItems.forEach(sheet => {
            sheet.classList.remove('expanded');
        });
    }

    function setActiveTocItem(activeLink) {
        // Remove active class from all TOC links
        document.querySelectorAll('.toc-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to clicked link
        activeLink.classList.add('active');
    }

    function setActiveTopicItem(activeTopic) {
        // Remove active class from all topics
        document.querySelectorAll('.topic-item').forEach(topic => {
            topic.classList.remove('active');
        });
        
        // Add active class to clicked topic
        activeTopic.classList.add('active');
    }

    function updateCurrentSectionDisplay(sectionTitle) {
        const currentSectionEl = document.getElementById('currentSection');
        if (currentSectionEl) {
            currentSectionEl.textContent = sectionTitle;
        }
    }

    function updateProgress() {
        // Simulate progress calculation
        const progressCircle = document.getElementById('progressCircle');
        const progressText = document.getElementById('progressText');
        
        // Calculate progress based on completed sections
        let progress = 0;
        if (currentSection !== 'overview') {
            progress = 50; // Simulate 50% progress when in content
        }
        
        const circumference = 2 * Math.PI * 45;
        const offset = circumference - (progress / 100) * circumference;
        
        if (progressCircle) {
            progressCircle.style.strokeDasharray = circumference;
            progressCircle.style.strokeDashoffset = offset;
        }
        if (progressText) {
            progressText.textContent = `${progress}%`;
        }
    }

    function updateNavigationButtons() {
        // Enable/disable navigation buttons based on current position
        if (prevBtn) {
            prevBtn.disabled = currentSection === 'overview';
        }
        
        if (nextBtn) {
            // Disable next button if on last section
            nextBtn.disabled = false;
        }
    }

    // Initialize the LMS
    initLMS();
});
class ProgressTracker {
    constructor(moduleId, userId) {
        this.moduleId = moduleId;
        this.userId = userId;
        this.progress = {};
    }

    async loadProgress() {
        try {
            const response = await fetch(`/modules/${this.moduleId}/progress`);
            const data = await response.json();
            this.progress = data;
            this.updateUI();
            return data;
        } catch (error) {
            console.error('Error loading progress:', error);
        }
    }

    updateUI() {
        // Update progress circle
        const progressCircle = document.getElementById('progressCircle');
        const progressText = document.getElementById('progressText');
        const progress = this.progress.overall_progress || 0;

        if (progressCircle) {
            const circumference = 2 * Math.PI * 45;
            const offset = circumference - (progress / 100) * circumference;
            progressCircle.style.strokeDasharray = `${circumference} ${circumference}`;
            progressCircle.style.strokeDashoffset = offset;
        }

        if (progressText) {
            progressText.textContent = `${Math.round(progress)}%`;
        }

        // Update TOC items with progress indicators
        this.updateTOCProgress();
    }

    updateTOCProgress() {
        // Update information sheets
        document.querySelectorAll('.information-sheet-item').forEach(item => {
            const sheetId = item.querySelector('.sheet-header').dataset.sheetId;
            const sheetProgress = this.getSheetProgress(sheetId);
            
            this.addProgressIndicator(item, sheetProgress);
        });

        // Update topics
        document.querySelectorAll('.topic-item').forEach(item => {
            const topicId = item.dataset.topicId;
            const topicProgress = this.getTopicProgress(topicId);
            
            this.addProgressIndicator(item, topicProgress);
        });
    }

    getSheetProgress(sheetId) {
        const sheetProgress = this.progress.detailed_progress?.['App\\Models\\InformationSheet'];
        if (!sheetProgress) return null;

        const progress = sheetProgress.find(p => p.progressable_id == sheetId);
        return progress ? progress.status : 'not_started';
    }

    getTopicProgress(topicId) {
        const topicProgress = this.progress.detailed_progress?.['App\\Models\\Topic'];
        if (!topicProgress) return null;

        const progress = topicProgress.find(p => p.progressable_id == topicId);
        return progress ? progress.status : 'not_started';
    }

    addProgressIndicator(element, status) {
        // Remove existing indicators
        element.querySelectorAll('.progress-indicator').forEach(ind => ind.remove());

        let indicator = '';
        switch(status) {
            case 'completed':
            case 'passed':
                indicator = '<span class="progress-indicator completed"><i class="fas fa-check"></i></span>';
                break;
            case 'in_progress':
                indicator = '<span class="progress-indicator in-progress"><i class="fas fa-spinner"></i></span>';
                break;
            case 'failed':
                indicator = '<span class="progress-indicator failed"><i class="fas fa-times"></i></span>';
                break;
            default:
                indicator = '<span class="progress-indicator not-started"><i class="fas fa-circle"></i></span>';
        }

        element.insertAdjacentHTML('beforeend', indicator);
    }

    async markAsCompleted(progressableType, progressableId, data = {}) {
        try {
            const response = await fetch('/progress', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    module_id: this.moduleId,
                    progressable_type: progressableType,
                    progressable_id: progressableId,
                    ...data
                })
            });

            if (response.ok) {
                await this.loadProgress(); // Reload progress
            }
        } catch (error) {
            console.error('Error marking as completed:', error);
        }
    }
}

class LearningFlow {
    constructor(progressTracker) {
        this.progressTracker = progressTracker;
        this.currentSection = null;
    }

    async navigateToNext(section) {
        // Check if current section is completed
        if (!await this.isSectionCompleted(this.currentSection)) {
            this.showCompletionWarning();
            return false;
        }

        // Proceed to next section
        this.currentSection = section;
        return true;
    }

    async isSectionCompleted(section) {
        if (!section) return true;

        const progress = await this.progressTracker.getSectionProgress(section);
        return progress && ['completed', 'passed'].includes(progress.status);
    }

    showCompletionWarning() {
        // Show modal or notification
        alert('Please complete the current section before proceeding to the next one.');
    }

    enableNextButton() {
        const nextBtn = document.getElementById('sidebar-next');
        if (nextBtn) {
            nextBtn.disabled = false;
        }
    }

    disableNextButton() {
        const nextBtn = document.getElementById('sidebar-next');
        if (nextBtn) {
            nextBtn.disabled = true;
        }
    }
}
        <!-- Create Announcement Sidebar -->
        <div class="slide-sidebar" id="createAnnouncementSidebar">
            <div class="slide-sidebar-header">
                <h5>Create Announcement</h5>
                <button class="close-sidebar" id="closeAnnouncementSidebar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="slide-sidebar-content">
                <form method="POST" action="{{ route('private.announcements.store') }}" id="createAnnouncementForm">
                    @csrf

                    <div class="mb-3">
                        <label for="fab_title" class="form-label required-field">Title</label>
                        <input type="text" name="title" id="fab_title" class="form-control"
                               value="{{ old('title') }}" placeholder="Announcement title" required>
                    </div>

                    <div class="mb-3">
                        <x-rich-editor
                            name="content"
                            id="fab_content"
                            label="Content"
                            placeholder="Write your announcement..."
                            :value="old('content')"
                            toolbar="standard"
                            :height="150"
                            :required="true"
                        />
                    </div>

                    <div class="mb-3">
                        <label for="fab_publish_at" class="form-label">Publish Date</label>
                        <input type="datetime-local" name="publish_at" id="fab_publish_at" class="form-control"
                               value="{{ old('publish_at') }}">
                        <small class="form-text text-muted">Leave empty to publish immediately.</small>
                    </div>

                    <div class="mb-3">
                        <label for="fab_deadline" class="form-label">Deadline (Optional)</label>
                        <input type="datetime-local" name="deadline" id="fab_deadline" class="form-control"
                               value="{{ old('deadline') }}">
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fab_is_urgent" name="is_urgent" value="1"
                                       {{ old('is_urgent') ? 'checked' : '' }}>
                                <label class="form-check-label" for="fab_is_urgent">Mark as Urgent</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fab_is_pinned" name="is_pinned" value="1"
                                       {{ old('is_pinned') ? 'checked' : '' }}>
                                <label class="form-check-label" for="fab_is_pinned">Pin to Top</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Create Announcement</button>
                    </div>
                </form>
            </div>
        </div>

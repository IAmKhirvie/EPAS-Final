<!-- Left Sidebar -->
<aside class="sidebar collapsed" id="sidebar">
    <div class="sidebar-content">
        <!-- User Profile Card -->
        @php $user = Auth::user(); @endphp
        <form id="avatar-form" action="{{ dynamic_route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" style="display: none;">
            @csrf
            <input type="hidden" name="cropped_image" id="sidebar-cropped-image">
            <input type="file" id="avatar-upload" name="avatar" accept="image/png, image/jpeg, image/gif">
        </form>

        <div class="sidebar-profile" onclick="openProfilePhotoModal()" style="cursor: pointer;"
             data-tooltip="Change Photo">
            <div class="sidebar-profile__bg"
                 style="{{ $user->profile_image ? 'background-image: url(' . $user->profile_image_url . ');' : '' }}">
                <div class="sidebar-profile__overlay"></div>
                @if(!$user->profile_image)
                <span class="sidebar-profile__initials" id="sidebar-fallback">{{ $user->initials }}</span>
                @endif
                <div class="sidebar-profile__info">
                    <h3 class="sidebar-profile__name" id="sidebar-username">{{ $user->first_name }} {{ $user->last_name }}</h3>
                    <div class="sidebar-profile__meta">
                        <span class="sidebar-profile__role role-{{ $user->role }}" id="sidebar-role">{{ ucfirst($user->role) }}</span>
                        @if($user->student_id)
                        <span class="sidebar-profile__id" id="sidebar-employee-id">ID: {{ $user->student_id }}</span>
                        @endif
                    </div>
                    @if(!$user->hasVerifiedEmail())
                    <a href="{{ route('settings.index') }}#profile" class="email-verify-badge" title="Click to verify your email" onclick="event.stopPropagation();">
                        <i class="fas fa-exclamation-circle"></i> Email not verified
                    </a>
                    @endif
                </div>
            </div>
            {{-- Collapsed state: rounded-square photo --}}
            <div class="sidebar-profile__collapsed">
                <div class="sidebar-profile__collapsed-photo">
                    <img src="{{ $user->profile_image_url }}" alt="Avatar" id="sidebar-avatar" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <span class="avatar-fallback" style="display: {{ $user->profile_image ? 'none' : 'flex' }};">{{ $user->initials }}</span>
                </div>
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="sidebar-section">
            <div class="sidebar-label">Main Menu</div>
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" wire:navigate class="nav-item {{ Request::routeIs('dashboard', 'student.dashboard', 'admin.dashboard') ? 'active' : '' }}" data-tooltip="Dashboard">
                    <i class="fas fa-chart-bar"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('courses.index') }}" wire:navigate class="nav-item {{ Request::is('courses*') ? 'active' : '' }}" data-tooltip="Courses">
                    <i class="fas fa-book"></i>
                    <span>Courses</span>
                </a>

                <a href="{{ route('grades.index') }}" wire:navigate class="nav-item {{ Request::is('grades*') ? 'active' : '' }}" data-tooltip="Grades">
                    <i class="fas fa-graduation-cap"></i>
                    <span>{{ Auth::user()->role === \App\Constants\Roles::STUDENT ? 'My Grades' : 'Grades' }}</span>
                </a>


                @if(in_array(Auth::user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
                <a href="{{ route('analytics.dashboard') }}" wire:navigate class="nav-item {{ Request::is('analytics*') ? 'active' : '' }}" data-tooltip="Analytics">
                    <i class="fas fa-chart-pie"></i>
                    <span>Analytics</span>
                </a>
                @endif

                @if(Auth::user()->role === \App\Constants\Roles::STUDENT)
                <a href="{{ route('student.analytics') }}" wire:navigate class="nav-item {{ Request::is('student/analytics*') ? 'active' : '' }}" data-tooltip="My Analytics">
                    <i class="fas fa-chart-line"></i>
                    <span>My Analytics</span>
                </a>
                <a href="{{ route('student.classes') }}" wire:navigate class="nav-item {{ Request::is('student/classes*') ? 'active' : '' }}" data-tooltip="My Class">
                    <i class="fas fa-users"></i>
                    <span>My Class</span>
                </a>
                <a href="{{ route('credentials.index') }}" wire:navigate class="nav-item {{ Request::is('credentials*') || Request::is('certificates*') ? 'active' : '' }}" data-tooltip="My Credentials">
                    <i class="fas fa-award"></i>
                    <span>My Credentials</span>
                </a>
                @endif
            </nav>
        </div>

        <!-- Content Management for Admin and Instructors -->
        @if(in_array(Auth::user()->role, [\App\Constants\Roles::ADMIN, \App\Constants\Roles::INSTRUCTOR]))
        <div class="sidebar-section">
            <div class="sidebar-label">Content</div>
            <nav class="sidebar-nav">
                <a href="{{ route('content.management') }}" wire:navigate class="nav-item {{ Request::is('content-management*') ? 'active' : '' }}" data-tooltip="Content Management">
                    <i class="fas fa-cubes"></i>
                    <span>Content Management</span>
                </a>
            </nav>
        </div>
        @endif

        <!-- For Admin -->
        @if(Auth::user()->role === \App\Constants\Roles::ADMIN)
        <div class="sidebar-section">
            <div class="sidebar-label">Administration</div>
            <nav class="sidebar-nav">
                @php
                $pendingRegistrations = \App\Models\Registration::whereIn('status', ['pending', 'email_verified'])->count();
                @endphp
                <a href="{{ route('admin.registrations.index') }}" wire:navigate class="nav-item {{ Request::is('admin/registrations*') ? 'active' : '' }}" data-tooltip="Registrations">
                    <i class="fas fa-user-clock"></i>
                    <span>Registrations</span>
                    <span class="nav-badge" id="badge-registrations" style="{{ $pendingRegistrations > 0 ? '' : 'display:none' }}">{{ $pendingRegistrations }}</span>
                </a>

                <!-- Users with Flyout Sub-menu -->
                <div class="nav-item-flyout {{ Request::is('private/users*') || Request::is('private/students*') || Request::is('private/instructors*') ? 'active' : '' }}">
                    <a href="javascript:void(0)" class="nav-item has-flyout" data-tooltip="Users">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                        <i class="fas fa-chevron-right nav-arrow-right"></i>
                    </a>
                    <div class="flyout-menu">
                        <div class="flyout-header">Users</div>
                        <a href="{{ route('private.users.index') }}" wire:navigate class="flyout-item {{ Request::is('private/users*') && !Request::is('*instructors*') && !Request::is('*students*') ? 'active' : '' }}">
                            <i class="fas fa-users-cog"></i>
                            <span>All Users</span>
                        </a>
                        <a href="{{ route('private.students.index') }}" wire:navigate class="flyout-item {{ Request::is('private/students*') ? 'active' : '' }}">
                            <i class="fas fa-user-graduate"></i>
                            <span>Students</span>
                        </a>
                        <a href="{{ route('private.instructors.index') }}" wire:navigate class="flyout-item {{ Request::is('private/instructors*') ? 'active' : '' }}">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Instructors</span>
                        </a>
                    </div>
                </div>

                <!-- Classes with Flyout Sub-menu -->
                <div class="nav-item-flyout {{ Request::is('class-management*') || Request::is('enrollment-requests*') ? 'active' : '' }}">
                    <a href="javascript:void(0)" class="nav-item has-flyout" data-tooltip="Classes">
                        <i class="fas fa-chalkboard"></i>
                        <span>Classes</span>
                        <i class="fas fa-chevron-right nav-arrow-right"></i>
                    </a>
                    <div class="flyout-menu">
                        <div class="flyout-header">Classes</div>
                        <a href="{{ route('class-management.index') }}" wire:navigate class="flyout-item {{ Request::is('class-management*') ? 'active' : '' }}">
                            <i class="fas fa-sitemap"></i>
                            <span>All Classes</span>
                        </a>
                        <a href="{{ route('enrollment-requests.index') }}" wire:navigate class="flyout-item {{ Request::is('enrollment-requests*') ? 'active' : '' }}">
                            <i class="fas fa-user-plus"></i>
                            <span>Enrollments</span>
                        </a>
                    </div>
                </div>

            </nav>
        </div>
        @endif

        <!-- For Instructors -->
        @if(Auth::user()->role === \App\Constants\Roles::INSTRUCTOR)
        <div class="sidebar-section">
            <div class="sidebar-label">Teaching</div>
            <nav class="sidebar-nav">
                <a href="{{ route('class-management.index') }}" wire:navigate class="nav-item {{ Request::is('class-management*') ? 'active' : '' }}" data-tooltip="My Classes">
                    <i class="fas fa-chalkboard"></i>
                    <span>My Classes</span>
                </a>

                <a href="{{ route('private.students.index') }}" wire:navigate class="nav-item {{ Request::is('private/students*') ? 'active' : '' }}" data-tooltip="My Students">
                    <i class="fas fa-user-graduate"></i>
                    <span>My Students</span>
                </a>

                <a href="{{ route('enrollment-requests.index') }}" wire:navigate class="nav-item {{ Request::is('enrollment-requests*') ? 'active' : '' }}" data-tooltip="Enrollment Requests">
                    <i class="fas fa-user-plus"></i>
                    <span>Enrollments</span>
                </a>
            </nav>
        </div>
        @endif

        @if(Auth::user()->role === \App\Constants\Roles::ADMIN)
        <!-- Trash (admin only) -->
        <div class="sidebar-section">
            <nav class="sidebar-nav">
                @php
                    $trashedCount = $trashedCount ?? 0;
                @endphp
                <a href="{{ route('trash.index') }}" wire:navigate class="nav-item {{ Request::is('trash*') ? 'active' : '' }}" data-tooltip="Trash">
                    <i class="fas fa-trash-alt"></i>
                    <span>Trash</span>
                    <span class="nav-badge" id="badge-trash" style="{{ $trashedCount > 0 ? '' : 'display:none' }}">{{ $trashedCount }}</span>
                </a>
            </nav>
        </div>
        @else
        <!-- Help & Support (for students only) -->
        <div class="sidebar-section">
            <nav class="sidebar-nav">
                <a href="{{ route('help-support') }}" wire:navigate class="nav-item {{ Request::is('help-support*') ? 'active' : '' }}" data-tooltip="Help & Support">
                    <i class="fas fa-question-circle"></i>
                    <span>Help & Support</span>
                </a>
            </nav>
        </div>
        @endif
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>

{{-- Real-time badge polling --}}
<script>
(function() {
    function updateBadges() {
        fetch('{{ route("api.badge-counts") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(r => r.ok ? r.json() : null)
        .catch(() => null)
        .then(data => {
            if (!data) return;
            ['registrations', 'trash', 'enrollments'].forEach(key => {
                const badge = document.getElementById('badge-' + key);
                if (badge) {
                    badge.textContent = data[key] || 0;
                    badge.style.display = data[key] > 0 ? '' : 'none';
                }
            });
        });
    }
    // Poll every 10 seconds
    setInterval(updateBadges, 10000);
})();
</script>

<!-- Profile Photo Modal -->
<div class="modal fade" id="profilePhotoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i>Profile Photo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Mode Selection -->
                <div id="photoModeSelect" class="text-center py-4">
                    @if($user->profile_image)
                    <p class="text-muted mb-4">What would you like to do?</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <button type="button" class="btn btn-outline-primary btn-lg" onclick="showRepositionMode()">
                            <i class="fas fa-arrows-alt d-block mb-2" style="font-size: 2rem;"></i>
                            Reposition Current Photo
                        </button>
                        <button type="button" class="btn btn-outline-success btn-lg" onclick="showUploadMode()">
                            <i class="fas fa-upload d-block mb-2" style="font-size: 2rem;"></i>
                            Upload New Photo
                        </button>
                    </div>
                    @else
                    <p class="text-muted mb-4">Upload a profile photo</p>
                    <button type="button" class="btn btn-primary btn-lg" onclick="showUploadMode()">
                        <i class="fas fa-upload me-2"></i>Upload Photo
                    </button>
                    @endif
                </div>

                <!-- Reposition Mode (for existing photo) -->
                <div id="repositionMode" style="display: none;">
                    <div class="cropper-wrapper" style="max-height: 400px; overflow: hidden;">
                        <img id="repositionImage" src="{{ $user->profile_image_url }}" alt="Reposition" style="max-width: 100%; display: block;">
                    </div>
                    <div class="mt-3">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperZoom(0.1)" title="Zoom In">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperZoom(-0.1)" title="Zoom Out">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperRotate(-45)" title="Rotate Left">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperRotate(45)" title="Rotate Right">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperReset()" title="Reset">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                        <p class="text-muted small mt-2 mb-0 text-center">
                            <i class="fas fa-info-circle me-1"></i>Drag to move, scroll to zoom
                        </p>
                    </div>
                </div>

                <!-- Upload Mode (for new photo) -->
                <div id="uploadMode" style="display: none;">
                    <div id="uploadDropzone" class="border-2 border-dashed rounded p-5 text-center" style="border-color: #dee2e6; cursor: pointer;">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="mb-2">Drag & drop your photo here</p>
                        <p class="text-muted small mb-3">or click to browse</p>
                        <input type="file" id="uploadModeInput" accept="image/jpeg,image/png,image/gif" style="display: none;">
                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('uploadModeInput').click()">
                            <i class="fas fa-folder-open me-1"></i>Browse Files
                        </button>
                        <p class="text-muted small mt-3 mb-0">JPG, PNG or GIF. Max 2MB.</p>
                    </div>
                </div>

                <!-- Cropper Mode (after selecting new photo) -->
                <div id="cropperMode" style="display: none;">
                    <div class="cropper-wrapper" style="max-height: 400px; overflow: hidden;">
                        <img id="cropperImage" src="" alt="Crop" style="max-width: 100%; display: block;">
                    </div>
                    <div class="mt-3">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperZoom(0.1)" title="Zoom In">
                                <i class="fas fa-search-plus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperZoom(-0.1)" title="Zoom Out">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperRotate(-45)" title="Rotate Left">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperRotate(45)" title="Rotate Right">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="sidebarCropperReset()" title="Reset">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                        <p class="text-muted small mt-2 mb-0 text-center">
                            <i class="fas fa-info-circle me-1"></i>Drag to move, scroll to zoom
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="photoModalBack" style="display: none;" onclick="showModeSelect()">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="photoModalSave" style="display: none;" onclick="saveCroppedPhoto()">
                    <i class="fas fa-check me-1"></i>Save Photo
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
    .cropper-view-box, .cropper-face { border-radius: 50%; }
    .cropper-view-box { box-shadow: 0 0 0 1px #39f; outline: 0; }
    #uploadDropzone.dragover { border-color: #0d6efd !important; background-color: rgba(13, 110, 253, 0.05); }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script>
if (typeof window._sidebarCropperInit === 'undefined') {
window._sidebarCropperInit = true;
var sidebarCropper = null;
var currentMode = 'select';

function openProfilePhotoModal() {
    showModeSelect();
    const modal = new bootstrap.Modal(document.getElementById('profilePhotoModal'));
    modal.show();
}

function showModeSelect() {
    currentMode = 'select';
    document.getElementById('photoModeSelect').style.display = 'block';
    document.getElementById('repositionMode').style.display = 'none';
    document.getElementById('uploadMode').style.display = 'none';
    document.getElementById('cropperMode').style.display = 'none';
    document.getElementById('photoModalBack').style.display = 'none';
    document.getElementById('photoModalSave').style.display = 'none';
    if (sidebarCropper) {
        sidebarCropper.destroy();
        sidebarCropper = null;
    }
}

function showRepositionMode() {
    currentMode = 'reposition';
    document.getElementById('photoModeSelect').style.display = 'none';
    document.getElementById('repositionMode').style.display = 'block';
    document.getElementById('uploadMode').style.display = 'none';
    document.getElementById('cropperMode').style.display = 'none';
    document.getElementById('photoModalBack').style.display = 'inline-block';
    document.getElementById('photoModalSave').style.display = 'inline-block';

    // Initialize cropper on existing image
    setTimeout(function() {
        const img = document.getElementById('repositionImage');
        if (sidebarCropper) sidebarCropper.destroy();
        sidebarCropper = new Cropper(img, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            cropBoxMovable: false,
            cropBoxResizable: false,
            guides: false,
            center: true,
            highlight: false,
            background: false,
            responsive: true,
        });
    }, 100);
}

function showUploadMode() {
    currentMode = 'upload';
    document.getElementById('photoModeSelect').style.display = 'none';
    document.getElementById('repositionMode').style.display = 'none';
    document.getElementById('uploadMode').style.display = 'block';
    document.getElementById('cropperMode').style.display = 'none';
    document.getElementById('photoModalBack').style.display = 'inline-block';
    document.getElementById('photoModalSave').style.display = 'none';
}

function showCropperMode(imageSrc) {
    currentMode = 'cropper';
    document.getElementById('photoModeSelect').style.display = 'none';
    document.getElementById('repositionMode').style.display = 'none';
    document.getElementById('uploadMode').style.display = 'none';
    document.getElementById('cropperMode').style.display = 'block';
    document.getElementById('photoModalBack').style.display = 'inline-block';
    document.getElementById('photoModalSave').style.display = 'inline-block';

    const img = document.getElementById('cropperImage');
    img.src = imageSrc;

    setTimeout(function() {
        if (sidebarCropper) sidebarCropper.destroy();
        sidebarCropper = new Cropper(img, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 1,
            cropBoxMovable: false,
            cropBoxResizable: false,
            guides: false,
            center: true,
            highlight: false,
            background: false,
            responsive: true,
        });
    }, 100);
}

function sidebarCropperZoom(ratio) {
    if (sidebarCropper) sidebarCropper.zoom(ratio);
}

function sidebarCropperRotate(degree) {
    if (sidebarCropper) sidebarCropper.rotate(degree);
}

function sidebarCropperReset() {
    if (sidebarCropper) sidebarCropper.reset();
}

function saveCroppedPhoto() {
    if (!sidebarCropper) return;

    const canvas = sidebarCropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });

    const croppedData = canvas.toDataURL('image/jpeg', 0.9);
    document.getElementById('sidebar-cropped-image').value = croppedData;

    // Close modal and submit form
    bootstrap.Modal.getInstance(document.getElementById('profilePhotoModal')).hide();
    document.getElementById('avatar-form').submit();
}

// File upload handling
document.addEventListener('DOMContentLoaded', function() {
    const uploadInput = document.getElementById('uploadModeInput');
    const dropzone = document.getElementById('uploadDropzone');

    if (uploadInput) {
        uploadInput.addEventListener('change', function(e) {
            handleFileSelect(e.target.files[0]);
        });
    }

    if (dropzone) {
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            handleFileSelect(file);
        });

        dropzone.addEventListener('click', function(e) {
            if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'INPUT') {
                document.getElementById('uploadModeInput').click();
            }
        });
    }

    function handleFileSelect(file) {
        if (!file) return;

        // Validate
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            return;
        }

        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, or GIF)');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            showCropperMode(e.target.result);
        };
        reader.readAsDataURL(file);
    }

    // Clean up when modal closes
    document.getElementById('profilePhotoModal').addEventListener('hidden.bs.modal', function() {
        if (sidebarCropper) {
            sidebarCropper.destroy();
            sidebarCropper = null;
        }
        document.getElementById('uploadModeInput').value = '';
    });
});
} // end sidebarCropper guard
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get sidebar element for positioning
        const sidebar = document.getElementById('sidebar');

        // ===== TOOLTIP POSITIONING FOR COLLAPSED SIDEBAR =====
        // Since tooltips use position:fixed to escape overflow clipping,
        // we need JS to set their vertical position on hover.
        function setupTooltipPositioning() {
            const navItems = sidebar.querySelectorAll('.nav-item[data-tooltip]');
            navItems.forEach(function(item) {
                item.addEventListener('mouseenter', function() {
                    if (!sidebar.classList.contains('collapsed')) return;
                    if (window.innerWidth < 1032) return; // no tooltips on mobile
                    const rect = this.getBoundingClientRect();
                    const centerY = rect.top + rect.height / 2;
                    // Set CSS custom properties for tooltip positioning
                    this.style.setProperty('--tooltip-top', centerY + 'px');
                });
            });

            // Also handle collapsed avatar tooltip
            const collapsedAvatar = sidebar.querySelector('.sidebar-profile__collapsed');
            if (collapsedAvatar) {
                collapsedAvatar.addEventListener('mouseenter', function() {
                    if (!sidebar.classList.contains('collapsed')) return;
                    if (window.innerWidth < 1032) return;
                    const rect = this.getBoundingClientRect();
                    const centerY = rect.top + rect.height / 2;
                    this.style.setProperty('--tooltip-top', centerY + 'px');
                });
            }
        }
        setupTooltipPositioning();

        // Handle flyout menu clicks
        document.querySelectorAll('.nav-item.has-flyout').forEach(function(trigger) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const flyoutGroup = this.closest('.nav-item-flyout');
                const flyoutMenu = flyoutGroup.querySelector('.flyout-menu');
                const isOpen = flyoutGroup.classList.contains('open');

                // Close all other flyouts first
                document.querySelectorAll('.nav-item-flyout.open').forEach(function(openFlyout) {
                    if (openFlyout !== flyoutGroup) {
                        openFlyout.classList.remove('open');
                    }
                });

                // Toggle current flyout
                if (isOpen) {
                    flyoutGroup.classList.remove('open');
                } else {
                    flyoutGroup.classList.add('open');
                    // Position handled by CSS - fixed at sidebar center
                }
            });
        });

        // Close flyouts when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav-item-flyout')) {
                document.querySelectorAll('.nav-item-flyout.open').forEach(function(openFlyout) {
                    openFlyout.classList.remove('open');
                });
            }
        });

        // Close flyouts when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.nav-item-flyout.open').forEach(function(openFlyout) {
                    openFlyout.classList.remove('open');
                });
            }
        });
    });
</script>
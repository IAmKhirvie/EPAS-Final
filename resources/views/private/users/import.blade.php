@extends('layouts.app')

@section('title', 'Import Users')

@section('content')
<div class="content-area">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-1">
                            <i class="fas fa-file-import me-2 text-primary"></i>Bulk Import Users
                        </h4>
                        <p class="text-muted mb-0">Import multiple users from a CSV or Excel file</p>
                    </div>
                    <a href="{{ route('private.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Users
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Import Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-upload me-2 text-primary"></i>Upload File
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('private.users.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf

                            <!-- File Upload -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-file-excel me-1 text-success"></i>Select File
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="upload-area border border-2 border-dashed rounded-3 p-4 text-center" id="uploadArea">
                                    <input type="file" name="file" id="fileInput" class="d-none" accept=".csv,.xlsx,.xls" required>
                                    <div id="uploadPlaceholder">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <p class="mb-2">Drag and drop your file here, or</p>
                                        <button type="button" class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                                            <i class="fas fa-folder-open me-1"></i>Browse Files
                                        </button>
                                        <p class="small text-muted mt-2 mb-0">Supported formats: CSV, XLSX, XLS (Max 5MB)</p>
                                    </div>
                                    <div id="uploadPreview" class="d-none">
                                        <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
                                        <p class="mb-1 fw-semibold" id="fileName"></p>
                                        <p class="small text-muted mb-2" id="fileSize"></p>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                                            <i class="fas fa-times me-1"></i>Remove
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Import Options -->
                            <h6 class="mb-3"><i class="fas fa-cog me-2"></i>Import Options</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Role <span class="text-danger">*</span></label>
                                    <select name="default_role" class="form-select" required>
                                        <option value="student" selected>Student</option>
                                        <option value="instructor">Instructor</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <small class="text-muted">Applied when role is not specified in the file</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Department</label>
                                    <select name="default_department_id" class="form-select">
                                        <option value="">-- None --</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Applied when department is not specified</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Password</label>
                                    <div class="input-group">
                                        <input type="text" name="default_password" class="form-control"
                                               placeholder="Leave empty for 'Password123!'" value="{{ old('default_password') }}">
                                        <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                                            <i class="fas fa-random"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Applied when password is not specified in the file</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label d-block">&nbsp;</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="auto_activate" id="autoActivate" value="1">
                                        <label class="form-check-label" for="autoActivate">
                                            <strong>Auto-activate users</strong>
                                            <br><small class="text-muted">Mark users as active and email verified</small>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Submit -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('private.users.import.template') }}" class="btn btn-outline-success">
                                    <i class="fas fa-download me-1"></i>Download Template
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-upload me-1"></i>Import Users
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-info"></i>Instructions
                        </h5>
                    </div>
                    <div class="card-body">
                        <ol class="mb-0">
                            <li class="mb-2">Download the template file to see the expected format</li>
                            <li class="mb-2">Fill in user data (first_name, last_name, and email are required)</li>
                            <li class="mb-2">Upload your completed file (CSV or Excel)</li>
                            <li class="mb-2">Configure default options for missing values</li>
                            <li>Click "Import Users" to process</li>
                        </ol>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">
                            <i class="fas fa-columns me-2 text-primary"></i>Supported Columns
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Column</th>
                                    <th>Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>first_name</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>last_name</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>email</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                </tr>
                                <tr>
                                    <td><code>middle_name</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>ext_name</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>password</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>role</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>student_id</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>section</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>room_number</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td><code>department</code></td>
                                    <td><span class="badge bg-secondary">No</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skipped Rows Report -->
        @if(session('import_skipped_rows'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm border-warning">
                    <div class="card-header bg-warning bg-opacity-10 py-3">
                        <h5 class="mb-0 text-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>Skipped Rows ({{ count(session('import_skipped_rows')) }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Row</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Errors</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(session('import_skipped_rows') as $skipped)
                                    <tr>
                                        <td>{{ $skipped['row'] }}</td>
                                        <td>{{ $skipped['data']['first_name'] ?? '' }} {{ $skipped['data']['last_name'] ?? '' }}</td>
                                        <td>{{ $skipped['data']['email'] ?? 'N/A' }}</td>
                                        <td>
                                            <ul class="mb-0 ps-3">
                                                @foreach($skipped['errors'] as $error)
                                                    <li class="text-danger small">{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .upload-area {
        transition: all 0.3s ease;
        cursor: pointer;
        background: #fafafa;
    }

    .upload-area:hover,
    .upload-area.dragover {
        border-color: var(--bs-primary) !important;
        background: rgba(79, 70, 229, 0.05);
    }

    .upload-area.dragover {
        transform: scale(1.02);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const uploadPreview = document.getElementById('uploadPreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const importForm = document.getElementById('importForm');
    const submitBtn = document.getElementById('submitBtn');

    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            showFilePreview(files[0]);
        }
    });

    uploadArea.addEventListener('click', (e) => {
        if (e.target === uploadArea || e.target.closest('#uploadPlaceholder')) {
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            showFilePreview(e.target.files[0]);
        }
    });

    function showFilePreview(file) {
        const validTypes = [
            'text/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        const validExtensions = ['.csv', '.xlsx', '.xls'];
        const ext = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

        if (!validExtensions.includes(ext)) {
            alert('Please select a valid file (CSV, XLSX, or XLS)');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            return;
        }

        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        uploadPlaceholder.classList.add('d-none');
        uploadPreview.classList.remove('d-none');
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    // Form submission
    importForm.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Importing...';
    });
});

function clearFile() {
    document.getElementById('fileInput').value = '';
    document.getElementById('uploadPlaceholder').classList.remove('d-none');
    document.getElementById('uploadPreview').classList.add('d-none');
}

function generatePassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.querySelector('input[name="default_password"]').value = password;
}
</script>
@endsection

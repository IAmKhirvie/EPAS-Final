@extends('layouts.app')

@section('title', 'Edit Certificate')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Certificate</h5>
                    <a href="{{ route('admin.certificates.index') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.certificates.update', $certificate) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="title" class="form-label">Certificate Title</label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', $certificate->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="certificate_number" class="form-label">Certificate Number</label>
                                <input type="text" class="form-control" value="{{ $certificate->certificate_number }}" disabled readonly>
                                <small class="text-muted">Auto-generated, cannot be changed</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description', $certificate->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">Student</label>
                                <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                    <option value="">-- Select Student --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $certificate->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="module_id" class="form-label">Module</label>
                                <select name="module_id" id="module_id" class="form-select @error('module_id') is-invalid @enderror" required>
                                    <option value="">-- Select Module --</option>
                                    @foreach($modules as $module)
                                        <option value="{{ $module->id }}" {{ old('module_id', $certificate->module_id) == $module->id ? 'selected' : '' }}>
                                            {{ $module->module_title }} ({{ $module->course->course_name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('module_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="issue_date" class="form-label">Issue Date</label>
                                <input type="date" name="issue_date" id="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                                       value="{{ old('issue_date', $certificate->issue_date?->format('Y-m-d')) }}">
                                @error('issue_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="template_used" class="form-label">Template</label>
                                <select name="template_used" id="template_used" class="form-select @error('template_used') is-invalid @enderror" required>
                                    @foreach($templates as $key => $label)
                                        <option value="{{ $key }}" {{ old('template_used', $certificate->template_used) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('template_used')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="pending_instructor" {{ old('status', $certificate->status) == 'pending_instructor' ? 'selected' : '' }}>Pending Instructor</option>
                                    <option value="pending_admin" {{ old('status', $certificate->status) == 'pending_admin' ? 'selected' : '' }}>Pending Admin</option>
                                    <option value="issued" {{ old('status', $certificate->status) == 'issued' ? 'selected' : '' }}>Issued</option>
                                    <option value="revoked" {{ old('status', $certificate->status) == 'revoked' ? 'selected' : '' }}>Revoked</option>
                                    <option value="rejected" {{ old('status', $certificate->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <div>
                                @if($certificate->status === 'issued')
                                    <a href="{{ route('admin.certificates.regenerate-pdf', $certificate) }}" class="btn btn-outline-secondary me-2"
                                       onclick="event.preventDefault(); document.getElementById('regenerate-form').submit();">
                                        <i class="fas fa-sync me-1"></i> Regenerate PDF
                                    </a>
                                    <a href="{{ route('admin.certificates.resend-email', $certificate) }}" class="btn btn-outline-info"
                                       onclick="event.preventDefault(); document.getElementById('resend-form').submit();">
                                        <i class="fas fa-envelope me-1"></i> Resend Email
                                    </a>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($certificate->status === 'issued')
                        <form id="regenerate-form" action="{{ route('admin.certificates.regenerate-pdf', $certificate) }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        <form id="resend-form" action="{{ route('admin.certificates.resend-email', $certificate) }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endif
                </div>
            </div>

            {{-- Certificate Info Card --}}
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Certificate Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Created:</strong> {{ $certificate->created_at->format('M d, Y H:i') }}</p>
                            <p class="mb-1"><strong>Last Updated:</strong> {{ $certificate->updated_at->format('M d, Y H:i') }}</p>
                            @if($certificate->instructor_approved_at)
                                <p class="mb-1"><strong>Instructor Approved:</strong> {{ $certificate->instructor_approved_at->format('M d, Y H:i') }}</p>
                            @endif
                            @if($certificate->admin_approved_at)
                                <p class="mb-1"><strong>Admin Approved:</strong> {{ $certificate->admin_approved_at->format('M d, Y H:i') }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($certificate->metadata)
                                <p class="mb-1"><strong>Email Sent:</strong>
                                    @if($certificate->metadata['email_sent'] ?? false)
                                        <span class="badge bg-success">Yes</span>
                                        <small class="text-muted">({{ $certificate->metadata['email_sent_at'] ?? 'N/A' }})</small>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </p>
                            @endif
                            @if($certificate->pdf_path)
                                <p class="mb-1"><strong>PDF:</strong>
                                    <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="card shadow-sm border-danger mt-4">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Deleting a certificate is permanent and cannot be undone.</p>
                    <form action="{{ route('admin.certificates.destroy', $certificate) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this certificate? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Delete Certificate
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

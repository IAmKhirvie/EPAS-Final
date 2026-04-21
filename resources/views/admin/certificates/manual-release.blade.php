@extends('layouts.app')

@section('title', 'Manual Certificate Release')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Manual Certificate Release</h5>
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

                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        Use this form to manually issue certificates. This bypasses the normal approval workflow and should only be used for testing or special cases.
                    </p>

                    {{-- Single Certificate Release --}}
                    <form action="{{ route('admin.certificates.manual-release.store') }}" method="POST" class="mb-5">
                        @csrf
                        <h6 class="border-bottom pb-2 mb-3">Issue Single Certificate</h6>

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Student</label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">-- Select Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->email }})</option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="module_id" class="form-label">Module</label>
                            <select name="module_id" id="module_id" class="form-select @error('module_id') is-invalid @enderror" required>
                                <option value="">-- Select Module --</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->module_title }} ({{ $module->course->course_name ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            @error('module_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-certificate me-1"></i> Issue Certificate
                        </button>
                    </form>

                    {{-- Bulk Certificate Release --}}
                    <form action="{{ route('admin.certificates.bulk-release') }}" method="POST">
                        @csrf
                        <h6 class="border-bottom pb-2 mb-3">Issue All Module Certificates (Bulk)</h6>
                        <p class="text-muted small mb-3">
                            This will issue certificates for ALL available modules to the selected student.
                        </p>

                        <div class="mb-3">
                            <label for="bulk_user_id" class="form-label">Student</label>
                            <select name="user_id" id="bulk_user_id" class="form-select" required>
                                <option value="">-- Select Student --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-warning" onclick="return confirm('This will issue {{ $modules->count() }} certificates. Continue?')">
                            <i class="fas fa-layer-group me-1"></i> Issue All Certificates ({{ $modules->count() }} modules)
                        </button>
                    </form>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h6 class="mb-3">Quick Links</h6>
                    <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-primary btn-sm me-2">
                        <i class="fas fa-list me-1"></i> All Certificates
                    </a>
                    <a href="{{ route('admin.certificates.pending') }}" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-clock me-1"></i> Pending Approvals
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

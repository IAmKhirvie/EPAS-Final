@extends('layouts.app')

@section('title', 'Certificate Management')

@section('content')
<div class="container py-4">
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-certificate me-2"></i>Certificate Management</h1>
            <p>Manage and release student certificates</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('admin.certificates.pending') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-clock me-1"></i> Pending Approvals
            </a>
            <a href="{{ route('admin.certificates.manual-release') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Manual Release
            </a>
        </div>
    </div>

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

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($certificates->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No certificates have been issued yet.</p>
                    <a href="{{ route('admin.certificates.manual-release') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Issue First Certificate
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Certificate #</th>
                                <th>Student</th>
                                <th>Course / Module</th>
                                <th>Status</th>
                                <th>Issue Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($certificates as $certificate)
                                <tr>
                                    <td>
                                        <code class="small">{{ $certificate->certificate_number }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ $certificate->user->full_name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $certificate->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        {{ $certificate->course->course_name ?? 'N/A' }}
                                        @if($certificate->module)
                                            <br><small class="text-muted">{{ $certificate->module->module_title }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($certificate->status)
                                            @case('issued')
                                                <span class="badge bg-success">Issued</span>
                                                @break
                                            @case('pending_instructor')
                                                <span class="badge bg-warning">Pending Instructor</span>
                                                @break
                                            @case('pending_admin')
                                                <span class="badge bg-info">Pending Admin</span>
                                                @break
                                            @case('revoked')
                                                <span class="badge bg-danger">Revoked</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-dark">Rejected</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($certificate->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($certificate->issue_date)
                                            {{ $certificate->issue_date->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.certificates.show', $certificate) }}" class="btn btn-outline-secondary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.certificates.edit', $certificate) }}" class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($certificate->status === 'issued')
                                                <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-outline-success" title="Download PDF">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form action="{{ route('admin.certificates.resend-email', $certificate) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-info" title="Send Email">
                                                        <i class="fas fa-envelope"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.certificates.destroy', $certificate) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this certificate? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $certificates->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

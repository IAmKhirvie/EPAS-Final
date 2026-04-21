@extends('layouts.app')

@section('title', 'Pending Certificate Approvals')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-clock text-warning me-2"></i>Pending Certificate Approvals</h4>
        <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to All Certificates
        </a>
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

    {{-- Pending Instructor Approval --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Awaiting Instructor Approval ({{ $pendingInstructor->count() }})</h5>
        </div>
        <div class="card-body">
            @if($pendingInstructor->isEmpty())
                <p class="text-muted text-center py-3 mb-0">No certificates pending instructor approval.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Module</th>
                                <th>Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingInstructor as $certificate)
                                <tr>
                                    <td>
                                        <strong>{{ $certificate->user->full_name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $certificate->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        {{ $certificate->module->module_title ?? 'N/A' }}
                                        <br><small class="text-muted">{{ $certificate->course->course_name ?? '' }}</small>
                                    </td>
                                    <td>
                                        {{ $certificate->created_at->diffForHumans() }}
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.certificates.instructor-approve', $certificate) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-check me-1"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $certificate->id }}" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>

                                        {{-- Reject Modal --}}
                                        <div class="modal fade" id="rejectModal{{ $certificate->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.certificates.reject', $certificate) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Certificate</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Reason (optional)</label>
                                                                <textarea name="reason" class="form-control" rows="3" placeholder="Enter rejection reason..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Certificate</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Pending Admin Approval --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-info bg-opacity-10">
            <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Awaiting Admin Approval ({{ $pendingAdmin->count() }})</h5>
        </div>
        <div class="card-body">
            @if($pendingAdmin->isEmpty())
                <p class="text-muted text-center py-3 mb-0">No certificates pending admin approval.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Module</th>
                                <th>Instructor Approved</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingAdmin as $certificate)
                                <tr>
                                    <td>
                                        <strong>{{ $certificate->user->full_name ?? 'N/A' }}</strong>
                                        <br><small class="text-muted">{{ $certificate->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        {{ $certificate->module->module_title ?? 'N/A' }}
                                        <br><small class="text-muted">{{ $certificate->course->course_name ?? '' }}</small>
                                    </td>
                                    <td>
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                        {{ $certificate->instructorApprovedBy->full_name ?? 'N/A' }}
                                        <br><small class="text-muted">{{ $certificate->instructor_approved_at?->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.certificates.admin-approve', $certificate) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary" title="Approve & Issue">
                                                <i class="fas fa-certificate me-1"></i> Issue Certificate
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectAdminModal{{ $certificate->id }}" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>

                                        {{-- Reject Modal --}}
                                        <div class="modal fade" id="rejectAdminModal{{ $certificate->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.certificates.reject', $certificate) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject Certificate</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Reason (optional)</label>
                                                                <textarea name="reason" class="form-control" rows="3" placeholder="Enter rejection reason..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Certificate</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

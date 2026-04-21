{{-- Pending Submissions Widget for Instructors/Admins --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-inbox text-warning me-2"></i>Pending Submissions
                    @if($submissions->count() > 0)
                        <span class="badge bg-warning text-dark ms-2">{{ $submissions->count() }}</span>
                    @endif
                </h6>
            </div>
            <div class="card-body p-0">
                @if($submissions->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%;">Student</th>
                                    <th style="width: 25%;">Activity</th>
                                    <th style="width: 20%;">Module</th>
                                    <th style="width: 15%;">Submitted</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submissions as $submission)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($submission['student_avatar'])
                                                    <img src="{{ $submission['student_avatar'] }}"
                                                         class="rounded-circle me-2"
                                                         width="32" height="32"
                                                         alt="{{ $submission['student_name'] }}">
                                                @else
                                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2"
                                                         style="width: 32px; height: 32px; font-size: 14px;">
                                                        {{ strtoupper(substr($submission['student_name'], 0, 1)) }}
                                                    </div>
                                                @endif
                                                <span>{{ $submission['student_name'] }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="submission-icon me-2"
                                                     style="background-color: {{ $submission['color'] }}20; color: {{ $submission['color'] }}; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="{{ $submission['icon'] }}" style="font-size: 12px;"></i>
                                                </div>
                                                <div>
                                                    <span class="d-block">{{ $submission['title'] }}</span>
                                                    <small class="text-muted text-capitalize">{{ str_replace('_', ' ', $submission['type']) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $submission['module'] }}</small>
                                        </td>
                                        <td>
                                            @if($submission['submitted_at'])
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($submission['submitted_at'])->diffForHumans() }}
                                                </small>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($submission['url'] && $submission['url'] !== '#')
                                                <a href="{{ $submission['url'] }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Review
                                                </a>
                                            @else
                                                <span class="badge bg-secondary">Coming Soon</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-check-circle fa-2x text-success mb-3"></i>
                        <p class="mb-0">All caught up! No pending submissions to review.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@extends('layouts.app')

@section('title', 'My Credentials')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="page-header">
        <div class="page-header-left">
            <h1><i class="fas fa-award me-2"></i>My Credentials</h1>
            <p>View your earned certificates and achievements</p>
        </div>
    </div>

    {{-- Stats Summary --}}
    <div class="page-stat-cards">
        <div class="page-stat-card orange">
            <div class="stat-decor"></div>
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-value">{{ number_format($stats['total_points']) }}</div>
            <div class="stat-label">Total Points</div>
        </div>
        <div class="page-stat-card red">
            <div class="stat-decor"></div>
            <div class="stat-icon"><i class="fas fa-fire"></i></div>
            <div class="stat-value">{{ $stats['current_streak'] }}</div>
            <div class="stat-label">Day Streak</div>
        </div>
        <div class="page-stat-card emerald">
            <div class="stat-decor"></div>
            <div class="stat-icon"><i class="fas fa-trophy"></i></div>
            <div class="stat-value">#{{ $stats['rank'] }}</div>
            <div class="stat-label">Rank</div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="certificates-tab" data-bs-toggle="tab" data-bs-target="#certificates" type="button" role="tab">
                <i class="fas fa-certificate me-1"></i> Certificates
                @if($certificates->count() > 0)
                <span class="badge bg-primary ms-1">{{ $certificates->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="achievements-tab" data-bs-toggle="tab" data-bs-target="#achievements" type="button" role="tab">
                <i class="fas fa-medal me-1"></i> Achievements
                @if(isset($achievements))
                <span class="badge bg-success ms-1">{{ $achievements->where('earned', true)->count() }}/{{ $achievements->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="leaderboard-tab" data-bs-toggle="tab" data-bs-target="#leaderboard" type="button" role="tab">
                <i class="fas fa-trophy me-1"></i> Leaderboard
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Certificates Tab --}}
        <div class="tab-pane fade show active" id="certificates" role="tabpanel">
            @if($certificates->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-certificate fa-4x text-muted mb-3"></i>
                    <h5>No Certificates Yet</h5>
                    <p class="text-muted">Complete courses to earn certificates.</p>
                    <a href="{{ route('courses.index') }}" class="btn btn-primary">
                        Browse Courses
                    </a>
                </div>
            </div>
            @else
            <div class="row">
                @foreach($certificates as $certificate)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="certificate-icon me-3">
                                    <i class="fas fa-certificate fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $certificate->course->course_name }}</h6>
                                    <small class="text-muted">Issued {{ $certificate->issue_date->format('M d, Y') }}</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">{{ $certificate->certificate_number }}</small>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('certificates.view', $certificate) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    <a href="{{ route('certificates.download', $certificate) }}" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-download me-1"></i> PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if(method_exists($certificates, 'hasPages') && $certificates->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $certificates->links() }}
            </div>
            @endif
            @endif
        </div>

        {{-- Achievements Tab — 9 categories, one card each showing current tier + next goal --}}
        <div class="tab-pane fade" id="achievements" role="tabpanel">
            @if(isset($achievements) && $achievements->isNotEmpty())
            @php
                $grouped = \App\Constants\Achievements::grouped();
                $tierColors = ['bronze' => '#cd7f32', 'silver' => '#c0c0c0', 'gold' => '#ffd700'];
                $tierBg = ['bronze' => 'rgba(205,127,50,0.1)', 'silver' => 'rgba(192,192,192,0.1)', 'gold' => 'rgba(255,215,0,0.1)'];
                $tierOrder = ['bronze', 'silver', 'gold'];
                $earnedMap = $achievements->keyBy('key');
            @endphp

            <div class="row">
            @foreach($grouped as $groupKey => $tiers)
                @php
                    $tiersArr = collect($tiers);
                    // Find highest earned tier
                    $highestEarned = null;
                    $nextTier = null;
                    foreach ($tiersArr as $key => $def) {
                        $a = $earnedMap->get($key);
                        if ($a && $a['earned']) {
                            $highestEarned = ['key' => $key, 'def' => $def, 'earned_at' => $a['earned_at']];
                        } else {
                            if (!$nextTier) $nextTier = ['key' => $key, 'def' => $def];
                        }
                    }
                    // Display tier: show highest earned, or first (locked) if none earned
                    $display = $highestEarned ?? ['key' => $tiersArr->keys()->first(), 'def' => $tiersArr->first(), 'earned_at' => null];
                    $tier = $display['def']['tier'] ?? 'bronze';
                    $color = $tierColors[$tier];
                    $bg = $tierBg[$tier];
                    $isEarned = $highestEarned !== null;
                @endphp
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 {{ !$isEarned ? 'opacity-50' : '' }}" style="border-radius:16px;border:1px solid {{ $isEarned ? $color : '#e8e8e8' }};border-left:4px solid {{ $color }};">
                        <div class="card-body d-flex align-items-start gap-3 py-3">
                            <div style="width:44px;height:44px;border-radius:12px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="{{ $display['def']['icon'] }}" style="font-size:1.1rem;color:{{ $isEarned ? $color : '#ccc' }};"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <strong style="font-size:0.88rem;">{{ $display['def']['name'] }}</strong>
                                    <span style="font-size:0.52rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:{{ $color }};background:{{ $bg }};padding:0.1rem 0.35rem;border-radius:3px;">{{ $tier }}</span>
                                </div>
                                <p class="mb-1" style="font-size:0.75rem;color:#888;">{{ $display['def']['description'] }}</p>
                                @if($isEarned)
                                <div style="font-size:0.65rem;color:#198754;"><i class="fas fa-check-circle me-1"></i>Earned +{{ $display['def']['points'] }}pts · {{ \Carbon\Carbon::parse($display['earned_at'])->format('M d, Y') }}</div>
                                @else
                                <div style="font-size:0.65rem;color:#ccc;"><i class="fas fa-lock me-1"></i>+{{ $display['def']['points'] }}pts</div>
                                @endif

                                {{-- Next tier goal --}}
                                @if($nextTier)
                                <div class="mt-2 pt-2" style="border-top:1px solid #f0f0f0;">
                                    @php $nt = $nextTier['def']; $ntColor = $tierColors[$nt['tier']]; @endphp
                                    <div style="font-size:0.65rem;color:#aaa;">
                                        <i class="fas fa-arrow-up me-1" style="color:{{ $ntColor }};"></i>
                                        <strong style="color:{{ $ntColor }};">{{ ucfirst($nt['tier']) }}</strong>: {{ $nt['description'] }} (+{{ $nt['points'] }}pts)
                                    </div>
                                </div>
                                @elseif($isEarned && $tier === 'gold')
                                <div class="mt-2 pt-2" style="border-top:1px solid #f0f0f0;">
                                    <div style="font-size:0.65rem;color:#ffd700;"><i class="fas fa-trophy me-1"></i>Max tier reached!</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            </div>

            @else
            <x-empty-state icon="fas fa-medal" title="No Achievements" description="Achievements will appear as you progress." />
            @endif
        </div>

        {{-- Leaderboard Tab --}}
        <div class="tab-pane fade" id="leaderboard" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="mb-0"><i class="fas fa-trophy text-warning me-2"></i>Top Students</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('credentials.index', ['leaderboard' => 'all']) }}#leaderboard"
                               class="btn btn-sm {{ $leaderboardFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-globe me-1"></i>All
                            </a>
                            @if(auth()->user()->section)
                            <a href="{{ route('credentials.index', ['leaderboard' => 'section']) }}#leaderboard"
                               class="btn btn-sm {{ $leaderboardFilter === 'section' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-users me-1"></i>My Section ({{ auth()->user()->section }})
                            </a>
                            @endif
                            @foreach($courses as $course)
                            <a href="{{ route('credentials.index', ['leaderboard' => 'course', 'course_id' => $course->id]) }}#leaderboard"
                               class="btn btn-sm {{ $leaderboardFilter === 'course' && request('course_id') == $course->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                                <i class="fas fa-book me-1"></i>{{ Str::limit($course->course_name, 20) }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($leaderboard->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No leaderboard data yet.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">Rank</th>
                                    <th>Student</th>
                                    <th class="text-end">Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaderboard as $index => $student)
                                @php $isCurrentUser = $student->id === auth()->id(); @endphp
                                <tr class="{{ $isCurrentUser ? 'table-primary' : '' }}">
                                    <td class="text-center">
                                        @if($index === 0)
                                        <span class="text-warning fs-5"><i class="fas fa-crown"></i></span>
                                        @elseif($index === 1)
                                        <span class="text-secondary fs-6"><i class="fas fa-medal"></i></span>
                                        @elseif($index === 2)
                                        <span style="color: #cd7f32;" class="fs-6"><i class="fas fa-medal"></i></span>
                                        @else
                                        <span class="text-muted fw-bold">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($student->profile_image)
                                            <img src="{{ $student->profile_image_url }}" alt="" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                                            @else
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <small class="text-muted fw-bold">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</small>
                                            </div>
                                            @endif
                                            <span class="{{ $isCurrentUser ? 'fw-bold' : '' }}">
                                                {{ $student->first_name }} {{ $student->last_name }}
                                                @if($isCurrentUser)
                                                <span class="badge bg-primary ms-1">You</span>
                                                @endif
                                                @if($student->section && $leaderboardFilter !== 'section')
                                                <span class="badge bg-light text-muted ms-1">{{ $student->section }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-warning">
                                            <i class="fas fa-star me-1"></i>{{ number_format($student->total_points) }}
                                        </span>
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

    </div>
</div>

@endsection

@section('scripts')
<style>
    .nav-tabs .nav-link {
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        border-color: #dee2e6 #dee2e6 #fff;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-activate tab based on hash or filter
        if (window.location.hash === '#leaderboard' || '{{ $leaderboardFilter }}' !== 'all') {
            const tab = document.getElementById('leaderboard-tab');
            if (tab) {
                const bsTab = new bootstrap.Tab(tab);
                bsTab.show();
            }
        } else if (window.location.hash === '#achievements') {
            const tab = document.getElementById('achievements-tab');
            if (tab) {
                const bsTab = new bootstrap.Tab(tab);
                bsTab.show();
            }
        }
    });
</script>
@endsection

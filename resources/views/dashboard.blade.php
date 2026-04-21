@extends('layouts.app')
@section('title', 'Dashboard - EPAS-E')
@push('styles')
<link rel="stylesheet" href="{{ dynamic_asset('css/pages/dashboard.css') }}">
@endpush

@php
    $user = Auth::user();
    $role = $user->role;
    $isStudent = $role === \App\Constants\Roles::STUDENT;
    $isAdmin = $role === \App\Constants\Roles::ADMIN;
    $isInstructor = $role === \App\Constants\Roles::INSTRUCTOR;

    // Single query: group by date for last 7 days
    $startDate = now()->subDays(6)->startOfDay();
    $progressQuery = \App\Models\UserProgress::where('updated_at', '>=', $startDate);
    if ($isStudent) {
        $progressQuery->where('user_id', $user->id);
    }
    $dailyCounts = $progressQuery
        ->selectRaw('DATE(updated_at) as d, COUNT(*) as c')
        ->groupByRaw('DATE(updated_at)')
        ->pluck('c', 'd');

    $chartPoints = [];
    $maxVal = $isStudent ? 10 : 20;
    for ($i = 6; $i >= 0; $i--) {
        $ds = now()->subDays($i)->toDateString();
        $chartPoints[] = min($dailyCounts->get($ds, 0), $maxVal);
    }
    $chartMax = max(max($chartPoints), 1);
@endphp

@section('content')
<div class="dash" id="dashGrid">

    {{-- ===== GRAPH ANALYTICS (row1-2, col1-2) ===== --}}
    <div class="dc g-graph">
        <div class="dc-head">
            <h4><i class="fas fa-chart-area"></i>
                @if($isStudent) Weekly Progress @elseif($isInstructor) Submissions This Week @else Active Users @endif
            </h4>
            <div class="dc-pills"><button class="dc-pill active">7D</button><button class="dc-pill">30D</button></div>
        </div>
        <div class="g-graph-body">
            <div class="g-summary">
                @if($isStudent)
                <div><div class="gs-v">{{ $student_progress ?? 0 }}%</div><div class="gs-l">Progress</div></div>
                <div><div class="gs-v">{{ $finished_activities ?? '0/0' }}</div><div class="gs-l">Completed</div></div>
                <div><div class="gs-v">{{ $average_grade ?? '0%' }}</div><div class="gs-l">Avg Grade</div></div>
                <div><div class="gs-v">{{ number_format($user->total_points ?? 0) }}</div><div class="gs-l">Points</div></div>
                @else
                <div><div class="gs-v">{{ $totalStudents ?? 0 }}</div><div class="gs-l">Students</div></div>
                <div><div class="gs-v">{{ $totalModules ?? 0 }}</div><div class="gs-l">Modules</div></div>
                <div><div class="gs-v">{{ $pendingEvaluations ?? 0 }}</div><div class="gs-l">Pending</div></div>
                @if($isAdmin)<div><div class="gs-v">{{ $pendingRegistrationsCount ?? 0 }}</div><div class="gs-l">Registrations</div></div>@endif
                @endif
            </div>
            <div class="g-chart">
                <div class="g-chart-y">@for($i = 5; $i >= 0; $i--)<span>{{ round($chartMax * $i / 5) }}</span>@endfor</div>
                <div class="g-chart-svg">
                    <svg viewBox="0 0 500 130" preserveAspectRatio="none">
                        <defs><linearGradient id="ag" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="var(--primary,#0c3a2d)" stop-opacity="0.18"/><stop offset="100%" stop-color="var(--primary,#0c3a2d)" stop-opacity="0.01"/></linearGradient></defs>
                        <line x1="0" y1="26" x2="500" y2="26" stroke="#e0e0e0" stroke-width="1"/>
                        <line x1="0" y1="52" x2="500" y2="52" stroke="#e0e0e0" stroke-width="1"/>
                        <line x1="0" y1="78" x2="500" y2="78" stroke="#e0e0e0" stroke-width="1"/>
                        <line x1="0" y1="104" x2="500" y2="104" stroke="#e0e0e0" stroke-width="1"/>
                        @php
                            $svgH = 130;
                            $coords = [];
                            foreach ($chartPoints as $i => $v) {
                                $x = round(($i / 6) * 500);
                                $y = round($svgH - ($v / $chartMax) * ($svgH - 10));
                                $coords[] = "{$x},{$y}";
                            }
                            $linePath = 'M' . implode(' L', $coords);
                            $areaPath = $linePath . " L500,{$svgH} L0,{$svgH}Z";
                            // Find peak point
                            $peakIdx = array_search(max($chartPoints), $chartPoints);
                            $peakX = round(($peakIdx / 6) * 500);
                            $peakY = round($svgH - (max($chartPoints) / $chartMax) * ($svgH - 10));
                        @endphp
                        <path d="{{ $areaPath }}" fill="url(#ag)"/>
                        <path d="{{ $linePath }}" fill="none" stroke="var(--primary,#0c3a2d)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        @php $dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']; @endphp
                        @foreach($chartPoints as $ci => $cv)
                        @php
                            $cx = round(($ci / 6) * 500);
                            $cy = round($svgH - ($cv / $chartMax) * ($svgH - 10));
                            $dayLabel = now()->subDays(6 - $ci)->format('D, M d');
                        @endphp
                        <circle class="chart-dot" cx="{{ $cx }}" cy="{{ $cy }}" r="3" fill="var(--primary,#0c3a2d)" stroke="none"
                            data-day="{{ $dayLabel }}" data-value="{{ $cv }}" style="cursor:pointer"/>
                        @endforeach
                    </svg>
                </div>
                <div class="g-chart-x"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
            </div>
        </div>
    </div>

    {{-- ===== RIGHT TOP: Pending + Calendar (row1-2, col3) ===== --}}
    <div class="g-rt">
        {{-- Pending --}}
        <div class="dc g-pend">
            <div class="dc-head">
                <h4><i class="fas fa-{{ $isStudent ? 'tasks' : ($isAdmin ? 'inbox' : 'clipboard-list') }}"></i>
                    Pending
                </h4>
                <span class="dc-badge" id="pendCount">{{ count($upcomingDeadlines ?? []) + ($isAdmin ? ($pendingRegistrationsCount ?? 0) : 0) }}</span>
            </div>
            <div class="g-pend-list" id="pendList">
                @if($isAdmin && isset($pendingRegistrations))
                    @foreach($pendingRegistrations->take(3) as $reg)
                    <a class="pr" href="{{ route('admin.registrations.show', $reg) }}" data-date="{{ $reg->email_verified_at?->toDateString() }}">
                        <div class="pr-icon" style="background:rgba(25,135,84,0.1);color:#198754"><i class="fas fa-user-plus"></i></div>
                        <div class="pr-i"><span class="pr-t">{{ $reg->full_name }}</span><span class="pr-s">Awaiting approval</span></div>
                        <span class="pr-d">{{ $reg->email_verified_at?->diffForHumans(null, true) ?? 'Pending' }}</span>
                    </a>
                    @endforeach
                @endif
                @foreach(collect($upcomingDeadlines ?? [])->take(5) as $dl)
                <a class="pr" href="{{ $dl['url'] ?? '#' }}" data-date="{{ \Carbon\Carbon::parse($dl['due_date'])->toDateString() }}">
                    <div class="pr-dot" style="background:{{ $dl['color'] }}"></div>
                    <div class="pr-i"><span class="pr-t">{{ $dl['title'] }}</span><span class="pr-s">{{ $dl['subtitle'] }}</span></div>
                    <span class="pr-d">{{ \Carbon\Carbon::parse($dl['due_date'])->format('d M, h:i A') }}</span>
                </a>
                @endforeach
                @if(empty($upcomingDeadlines) && (!$isAdmin || ($pendingRegistrationsCount ?? 0) === 0))
                <div class="d-empty"><i class="fas fa-check-circle" style="color:#198754"></i> All caught up!</div>
                @endif
            </div>
        </div>

        {{-- Calendar --}}
        <div class="dc g-cal">
            <div class="cal-top-bar">
                <span class="cal-month" id="calMonth">{{ now()->format('F Y') }}</span>
                <div class="cal-toggles">
                    <button class="cal-tg active" id="tgWeek">Week</button>
                    <button class="cal-tg" id="tgMonth">Month</button>
                </div>
            </div>
            <div class="cal-strip" id="stripView">
                <div class="cal-arr l" id="arrL"><i class="fas fa-chevron-left"></i></div>
                <div class="cal-strip-inner" id="stripInner"></div>
                <div class="cal-arr r" id="arrR"><i class="fas fa-chevron-right"></i></div>
            </div>
            <div class="cal-month-view" id="monthView"></div>
            <div class="cal-detail" id="calDet">
                <div class="cal-det-title" id="calDetTitle"></div>
                <div id="calDetItems"></div>
            </div>
        </div>
    </div>

    {{-- ===== ANNOUNCEMENTS TABLE (row3-4, col1-2) ===== --}}
    <div class="dc g-ann" id="annCard">
        <div class="dc-head">
            <h4><i class="fas fa-bullhorn"></i> Announcements</h4>
            <div style="display:flex;align-items:center;gap:0.4rem;">
                <div class="dc-pills">
                    <button class="dc-pill active" onclick="filterAnn('all')">All</button>
                    <button class="dc-pill" onclick="filterAnn('pinned')">Pinned</button>
                    <button class="dc-pill" onclick="filterAnn('urgent')">Urgent</button>
                </div>
                @if(!$isStudent)
                <a href="{{ route('private.announcements.create') }}" class="ann-action-btn" title="New Announcement" style="background:var(--primary,#0c3a2d);color:#fff;border-color:var(--primary,#0c3a2d);width:28px;height:28px;">
                    <i class="fas fa-plus"></i>
                </a>
                @endif
            </div>
        </div>
        <div class="g-ann-list">
            <table class="ann-table">
                <thead><tr><th>Author</th><th>Date & Time</th><th>Summary</th><th></th></tr></thead>
                <tbody>
                @forelse($recentAnnouncements ?? [] as $a)
                <tr onclick="window.location='{{ route('private.announcements.show', $a) }}'" data-pinned="{{ $a->is_pinned ? '1' : '0' }}" data-urgent="{{ $a->is_urgent ? '1' : '0' }}">
                    <td><div class="ann-user">
                        <div class="ann-av">@if($a->user && $a->user->profile_image)<img src="{{ $a->user->profile_image_url }}" alt="">@elseif($a->user)<span style="font-size:0.65rem;font-weight:700;color:var(--primary)">{{ $a->user->initials }}</span>@else<i class="fas fa-robot"></i>@endif</div>
                        <span class="ann-uname">{{ $a->user ? $a->user->full_name : 'EPAS-E System' }}</span>
                    </div></td>
                    <td><span class="ann-dt">{{ $a->created_at->format('h:i A') }} · {{ $a->created_at->format('M d') }}</span></td>
                    <td><span class="ann-summ">{{ Str::limit(strip_tags($a->content ?? ''), 60) }}</span></td>
                    <td class="ann-actions">
                        <a href="{{ route('private.announcements.show', $a) }}" class="ann-action-btn" title="View" onclick="event.stopPropagation()"><i class="fas fa-arrow-right"></i></a>
                        @if($isAdmin || $a->user_id === $user->id)
                        <a href="{{ route('private.announcements.edit', $a) }}" class="ann-action-btn" title="Edit" onclick="event.stopPropagation()"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('private.announcements.destroy', $a) }}" style="display:inline" onclick="event.stopPropagation()" onsubmit="return confirm('Delete this announcement?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="ann-action-btn" title="Delete" style="color:#dc3545"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4"><div class="d-empty"><i class="fas fa-bullhorn"></i> No announcements</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ann-footer">
            <button class="ann-more" id="annMore"><span id="annMoreText">Show more</span> <i class="fas fa-chevron-down"></i></button>
        </div>
    </div>

    {{-- ===== QUICK STATS (row3-4, col3) ===== --}}
    <div class="dc g-qs">
        <div class="dc-head"><h4><i class="fas fa-chart-pie"></i> Quick Stats</h4></div>
        <div class="g-qs-body">
            @if($isStudent)
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(12,58,45,0.1);color:#0c3a2d"><i class="fas fa-chart-line"></i></div><span class="qsr-lb">Progress</span></div><span class="qsr-v">{{ $student_progress ?? 0 }}%</span></div>
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(25,135,84,0.1);color:#198754"><i class="fas fa-check-double"></i></div><span class="qsr-lb">Completed</span></div><span class="qsr-v">{{ $finished_activities ?? '0/0' }}</span></div>
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(13,110,253,0.1);color:#0d6efd"><i class="fas fa-book-open"></i></div><span class="qsr-lb">Modules</span></div><span class="qsr-v">{{ $total_modules ?? 0 }}</span></div>
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(111,66,193,0.1);color:#6f42c1"><i class="fas fa-star"></i></div><span class="qsr-lb">Avg Grade</span></div><span class="qsr-v">{{ $average_grade ?? '0%' }}</span></div>
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(253,126,20,0.1);color:#fd7e14"><i class="fas fa-trophy"></i></div><span class="qsr-lb">Points</span></div><span class="qsr-v">{{ number_format($user->total_points ?? 0) }}</span></div>
            @else
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(12,58,45,0.1);color:#0c3a2d"><i class="fas fa-user-graduate"></i></div><span class="qsr-lb">Students</span></div><span class="qsr-v">{{ $totalStudents ?? 0 }}</span></div>
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(13,110,253,0.1);color:#0d6efd"><i class="fas fa-book-open"></i></div><span class="qsr-lb">Modules</span></div><span class="qsr-v">{{ $totalModules ?? 0 }}</span></div>
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(253,126,20,0.1);color:#fd7e14"><i class="fas fa-clock"></i></div><span class="qsr-lb">Pending</span></div><span class="qsr-v">{{ $pendingEvaluations ?? 0 }}</span></div>
            @if($isAdmin)
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(25,135,84,0.1);color:#198754"><i class="fas fa-user-plus"></i></div><span class="qsr-lb">Registrations</span></div><span class="qsr-v">{{ $pendingRegistrationsCount ?? 0 }}</span></div>
            @else
            <div class="qsr"><div class="qsr-l"><div class="qsr-ic" style="background:rgba(111,66,193,0.1);color:#6f42c1"><i class="fas fa-layer-group"></i></div><span class="qsr-lb">Sections</span></div><span class="qsr-v">{{ $ongoingBatches ?? 0 }}</span></div>
            @endif
            @endif
        </div>
    </div>
</div>
@endsection

@php
    $calDeadlineData = collect($upcomingDeadlines ?? [])->map(function($d) {
        return [
            'd' => \Carbon\Carbon::parse($d['due_date'])->toDateString(),
            't' => $d['title'],
            's' => $d['subtitle'],
            'c' => $d['color'],
            'tm' => \Carbon\Carbon::parse($d['due_date'])->format('h:i A'),
        ];
    })->values();
@endphp

@push('scripts')
<script>
(function() {
    const deadlines = @json($calDeadlineData);

    const today = new Date();
    let calOffset = 0;
    const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    function fmt(d) { return d.toISOString().split('T')[0]; }
    function getDl(ds) { return deadlines.filter(x => x.d === ds); }
    function getColors(ds) { return [...new Set(getDl(ds).map(x => x.c))]; }

    // Calendar strip
    function renderStrip() {
        const inner = document.getElementById('stripInner');
        const center = new Date(today); center.setDate(today.getDate() + calOffset);
        let h = '<div class="cal-page">';
        for (let i = -3; i <= 3; i++) {
            const d = new Date(center); d.setDate(center.getDate() + i);
            const ds = fmt(d), isT = ds === fmt(today), cols = getColors(ds);
            let bg = '';
            if (cols.length === 1) bg = `background:${cols[0]}35`;
            else if (cols.length > 1) bg = `background:linear-gradient(135deg,${cols.map(c=>c+'35').join(',')})`;
            if (isT && !cols.length) bg = '';
            h += `<div class="cday ${isT?'today':''}" data-date="${ds}" style="${bg}"><span class="cday-n">${d.getDate()}</span><span class="cday-l">${dayNames[d.getDay()]}</span>${cols.length?`<div class="cday-dots">${cols.slice(0,3).map(c=>`<span style="background:${c}"></span>`).join('')}</div>`:''}</div>`;
        }
        h += '</div>';
        inner.innerHTML = h;
        document.getElementById('calMonth').textContent = `${monthNames[center.getMonth()]} ${center.getFullYear()}`;
    }

    function renderMonthly() {
        const mv = document.getElementById('monthView');
        const y = today.getFullYear(), m = today.getMonth();
        const fd = new Date(y,m,1).getDay(), dim = new Date(y,m+1,0).getDate(), pd = new Date(y,m,0).getDate();
        let h = '<div class="cmg">';
        ['S','M','T','W','T','F','S'].forEach(d => h += `<div class="cmh">${d}</div>`);
        for (let i = fd-1; i >= 0; i--) h += `<div class="cmd other">${pd-i}</div>`;
        for (let d = 1; d <= dim; d++) {
            const ds = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const cols = getColors(ds);
            let bg = '';
            if (cols.length === 1) bg = `background:${cols[0]}35`;
            else if (cols.length > 1) bg = `background:linear-gradient(135deg,${cols.map(c=>c+'35').join(',')})`;
            h += `<div class="cmd ${d===today.getDate()?'today':''}" data-date="${ds}" style="${bg}">${d}</div>`;
        }
        for (let i = 1; i <= 42-(fd+dim); i++) h += `<div class="cmd other">${i}</div>`;
        h += '</div>';
        mv.innerHTML = h;
    }

    // Arrows
    document.getElementById('arrL').addEventListener('click', () => { calOffset--; renderStrip(); });
    document.getElementById('arrR').addEventListener('click', () => { calOffset++; renderStrip(); });

    // View toggle
    document.getElementById('tgWeek').addEventListener('click', () => {
        document.getElementById('tgWeek').classList.add('active');
        document.getElementById('tgMonth').classList.remove('active');
        document.getElementById('stripView').style.display = '';
        document.getElementById('monthView').classList.remove('active');
        document.getElementById('calDet').classList.remove('active');
    });
    document.getElementById('tgMonth').addEventListener('click', () => {
        document.getElementById('tgMonth').classList.add('active');
        document.getElementById('tgWeek').classList.remove('active');
        document.getElementById('stripView').style.display = 'none';
        document.getElementById('monthView').classList.add('active');
        renderMonthly();
        document.getElementById('calDet').classList.remove('active');
    });

    // Date click — show detail + filter pending
    document.addEventListener('click', (e) => {
        const el = e.target.closest('.cday, .cmd');
        if (!el || !el.dataset.date) return;
        const ds = el.dataset.date;
        const isToday = ds === fmt(today);

        document.querySelectorAll('.cday.sel, .cmd.sel').forEach(x => x.classList.remove('sel'));
        el.classList.add('sel');

        const items = getDl(ds);
        const det = document.getElementById('calDet');
        if (items.length) {
            const d = new Date(ds);
            document.getElementById('calDetTitle').textContent = `${monthNames[d.getMonth()].substring(0,3)} ${d.getDate()}, ${d.getFullYear()}`;
            document.getElementById('calDetItems').innerHTML = `<table class="cal-det-tbl">${items.map(p => `<tr><td style="width:20px"><span class="cdt-swatch" style="background:${p.c}"></span></td><td class="cdt-title">${p.t}</td><td class="cdt-time">${p.tm}</td></tr>`).join('')}</table>`;
            det.classList.add('active');
        } else {
            det.classList.remove('active');
        }

        // Filter pending: if today clicked, show all; otherwise filter to selected date
        document.querySelectorAll('.pr').forEach(r => {
            if (isToday) {
                r.style.display = '';
            } else {
                r.style.display = r.dataset.date === ds ? '' : 'none';
            }
        });
    });

    // Show more / collapse
    document.getElementById('annMore').addEventListener('click', () => {
        const card = document.getElementById('annCard');
        const grid = document.getElementById('dashGrid');
        const expanded = card.classList.toggle('expanded');
        grid.classList.toggle('expanded', expanded);
        document.getElementById('annMoreText').textContent = expanded ? 'Show less' : 'Show more';
    });

    // Init
    renderStrip();
    renderMonthly();
})();

// Announcement filter
function filterAnn(type) {
    document.querySelectorAll('.dc-pills .dc-pill').forEach(function(b) { b.classList.remove('active'); });
    event.target.classList.add('active');
    document.querySelectorAll('.ann-table tbody tr').forEach(function(row) {
        if (type === 'all') { row.style.display = ''; return; }
        if (type === 'pinned') { row.style.display = row.dataset.pinned === '1' ? '' : 'none'; }
        if (type === 'urgent') { row.style.display = row.dataset.urgent === '1' ? '' : 'none'; }
    });
}

// Chart dot tooltip
(function() {
    var tip = document.createElement('div');
    tip.className = 'chart-tip';
    tip.style.cssText = 'position:fixed;pointer-events:none;z-index:1080;background:var(--card,#fff);border:1px solid #e8e8e8;border-radius:12px;padding:0.55rem 0.75rem;box-shadow:0 8px 25px rgba(0,0,0,0.1);font-size:0.82rem;font-family:Plus Jakarta Sans,sans-serif;opacity:0;transition:opacity 0.15s;';
    document.body.appendChild(tip);

    var label = @json($isStudent ? 'Activities' : 'Submissions');

    document.querySelectorAll('.chart-dot').forEach(function(dot) {
        dot.addEventListener('mouseenter', function(e) {
            var day = this.dataset.day;
            var val = this.dataset.value;
            tip.innerHTML = '<div style="font-weight:700;margin-bottom:2px;">' + day + '</div><div style="color:#666;">' + val + ' ' + label + '</div>';
            tip.style.opacity = '1';
            var r = this.getBoundingClientRect();
            tip.style.left = (r.left + r.width/2 - tip.offsetWidth/2) + 'px';
            tip.style.top = (r.top - tip.offsetHeight - 8) + 'px';
        });
        dot.addEventListener('mouseleave', function() {
            tip.style.opacity = '0';
        });
    });
})();
</script>
@endpush

@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-truncate">Announcement Details</h5>
                    <a href="{{ route('private.announcements.index') }}" class="btn btn-sm btn-outline-primary">Back to Announcements</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <!-- Announcement -->
                    <div class="announcement-detail mb-4">
                        <div class="announcement-header mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h3 class="mb-2 announcement-title">
                                    @if($announcement->is_pinned)
                                    <i class="fas fa-thumbtack text-warning me-2" title="Pinned"></i>
                                    @endif
                                    {{ $announcement->title }}
                                    @if($announcement->is_urgent)
                                    <span class="badge bg-danger ms-2">URGENT</span>
                                    @endif
                                </h3>
                            </div>

                            <div class="announcement-meta text-muted">
                                <small>
                                    Posted by {{ $announcement->user ? $announcement->user->full_name : 'EPAS-E System' }}
                                    on {{ $announcement->created_at->format('F j, Y \a\t g:i A') }}
                                    @if($announcement->publish_at && $announcement->publish_at->isFuture())
                                    • Scheduled for {{ $announcement->publish_at->format('F j, Y \a\t g:i A') }}
                                    @endif
                                </small>
                            </div>
                        </div>

                        <div class="announcement-content mb-4">
                            <p class="lead">{{ $announcement->content }}</p>
                        </div>
                    </div>

                    <!-- Comments Section -->
                    <div class="comments-section">
                        <h5 class="mb-3">
                            Comments ({{ $announcement->comments->count() }})
                        </h5>

                        <!-- Comment Form -->
                        @auth
                        <div class="comment-form mb-4">
                            <form action="{{ route('private.announcements.comment', $announcement->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Add a comment:</label>
                                    <textarea name="comment" id="comment" class="form-control" rows="3"
                                        placeholder="Write your comment here..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                        </div>
                        @endauth

                        <!-- Comments List -->
                        <div class="comments-list">
                            @if($announcement->comments->count() > 0)
                            @foreach($announcement->comments as $comment)
                            <div class="comment-item mb-3 p-3 border rounded">
                                <div class="comment-header d-flex justify-content-between align-items-center mb-2">
                                    <div class="comment-author">
                                        <strong>{{ $comment->user ? $comment->user->full_name : 'Unknown' }}</strong>
                                    </div>
                                    <div class="comment-time text-muted">
                                        <small>{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <div class="comment-body">
                                    <p class="mb-0">{{ $comment->comment }}</p>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-comments fa-2x mb-2"></i>
                                <p>No comments yet. Be the first to comment!</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .announcement-title {
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
        line-height: 1.4;
    }

    .card-title.text-truncate {
        max-width: 70%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Responsive adjustments */
    @media (max-width: 1032px) {
        .announcement-title {
            font-size: 1.5rem;
            line-height: 1.3;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .card-header .btn {
            margin-top: 0.5rem;
            align-self: flex-end;
        }

        .card-title.text-truncate {
            max-width: 100%;
        }
    }

    @media (max-width: 576px) {
        .announcement-title {
            font-size: 1.25rem;
        }
    }
</style>
@endsection
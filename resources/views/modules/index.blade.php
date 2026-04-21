@extends('layouts.app')

@section('title', 'EPAS-E Courses')

@section('content')
<div class="container-fluid py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">EPAS-E Learning Courses</h1>
                @if(in_array(auth()->user()->role, ['admin', 'instructor']))
                <a href="{{ route('courses.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New Course
                </a>
                @endif
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                We've moved to a course-based structure. You are being redirected to the courses page.
            </div>

            <script>
                // Redirect to courses page after a brief delay
                setTimeout(function() {
                    window.location.href = "{{ route('courses.index') }}";
                }, 2000);
            </script>

            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Redirecting to courses...</p>
                <a href="{{ route('courses.index') }}" class="btn btn-primary mt-2">
                    Go to Courses Now
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
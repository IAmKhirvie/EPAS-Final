<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') - EPAS-E LMS</title>
    <!-- Google Fonts - Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ffb902;
            --bg: #f8f9fa;
            --text: #212529;
            --card-bg: #ffffff;
            --muted: #6c757d;
        }
        .dark-mode {
            --bg: #1a1d21;
            --text: #e9ecef;
            --card-bg: #2b2f35;
            --muted: #adb5bd;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 520px;
        }
        .error-code {
            font-size: 7rem;
            font-weight: 800;
            line-height: 1;
            color: var(--primary);
            opacity: 0.15;
            margin-bottom: -1rem;
        }
        .error-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .error-message {
            color: var(--muted);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.5rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .btn-home:hover { opacity: 0.9; color: #fff; }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.5rem;
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--muted);
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: opacity 0.2s;
            margin-left: 0.5rem;
        }
        .btn-back:hover { opacity: 0.7; color: var(--text); }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">@yield('code')</div>
        <div class="error-icon">
            <i class="@yield('icon', 'fas fa-exclamation-triangle')"></i>
        </div>
        <h1 class="error-title">@yield('title')</h1>
        <p class="error-message">@yield('message')</p>
        <div>
            <a href="{{ url('/dashboard') }}" class="btn-home">
                <i class="fas fa-home"></i> Go to Dashboard
            </a>
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
        </div>
    </div>
    <script>
        (function() {
            var saved = localStorage.getItem('theme');
            var sys = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && sys)) document.body.classList.add('dark-mode');
        })();
    </script>
</body>
</html>

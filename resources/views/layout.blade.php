<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marthina Learning</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @livewireStyles
    <style>
        :root {
            --kid-bg: #fff8ef;
            --kid-surface: rgba(255, 255, 255, 0.9);
            --kid-ink: #213547;
            --kid-muted: #6b7280;
            --kid-blue: #4f8dfd;
            --kid-yellow: #ffd166;
            --kid-pink: #ff8fab;
            --kid-green: #4ecb71;
            --kid-orange: #ff9f5a;
            --kid-shadow: 0 20px 45px rgba(79, 141, 253, 0.12);
        }
        body {
            background:
                radial-gradient(circle at top left, rgba(255, 209, 102, 0.35), transparent 24%),
                radial-gradient(circle at top right, rgba(255, 143, 171, 0.28), transparent 22%),
                linear-gradient(180deg, #f2f8ff 0%, var(--kid-bg) 52%, #fffdf8 100%);
            min-height: 100vh;
            font-family: 'Nunito', sans-serif;
            color: var(--kid-ink);
        }
        body::before,
        body::after {
            content: "";
            position: fixed;
            inset: auto;
            width: 320px;
            height: 320px;
            border-radius: 999px;
            z-index: -1;
            filter: blur(18px);
            opacity: 0.45;
        }
        body::before {
            top: -80px;
            left: -80px;
            background: rgba(79, 141, 253, 0.18);
        }
        body::after {
            right: -90px;
            bottom: -90px;
            background: rgba(255, 143, 171, 0.2);
        }
        .card {
            border: 0;
            border-radius: 28px;
            box-shadow: var(--kid-shadow);
            background: var(--kid-surface);
            backdrop-filter: blur(8px);
        }
        .btn-custom {
            background: linear-gradient(135deg, var(--kid-blue), #6da7ff);
            border: 0;
            border-radius: 999px;
            color: white;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(79, 141, 253, 0.28);
        }
        .btn-custom:hover {
            background: linear-gradient(135deg, #3b7ef7, #5d99fa);
            color: white;
            transform: translateY(-1px);
        }
        .quiz-image {
            border-radius: 24px;
            max-height: 200px;
            object-fit: cover;
            border: 6px solid rgba(255, 255, 255, 0.7);
        }
        .card-title, .card-text, h1, h2, h3, h4, h5, h6 {
            color: var(--kid-ink);
            font-family: 'Baloo 2', cursive;
        }
        .display-1, .display-4 {
            color: var(--kid-ink);
            font-weight: 700;
        }
        p,
        span,
        label,
        input,
        textarea,
        select,
        button,
        a,
        li {
            font-family: 'Nunito', sans-serif;
        }
        .badge {
            font-size: 0.9em;
            border-radius: 999px;
            padding: 0.5em 1em;
        }
        .navbar-brand {
            color: var(--kid-ink) !important;
            font-weight: 700;
        }
        .navbar {
            padding-top: 1rem;
        }
        .navbar .container {
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 22px;
            box-shadow: 0 10px 30px rgba(33, 53, 71, 0.08);
            padding: 1rem 1.25rem;
            backdrop-filter: blur(12px);
        }
        .text-muted {
            color: var(--kid-muted) !important;
        }
        .btn-outline-light,
        .btn-outline-secondary,
        .btn-light {
            border-radius: 999px;
            font-weight: 700;
        }
        .app-page {
            padding-bottom: 3rem;
        }
        .hero-card {
            overflow: hidden;
            position: relative;
        }
        .hero-card::after {
            content: "";
            position: absolute;
            inset: auto -40px -50px auto;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle, rgba(255, 209, 102, 0.45), transparent 70%);
            pointer-events: none;
        }
        .hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            background: rgba(79, 141, 253, 0.1);
            color: var(--kid-blue);
            font-size: 0.88rem;
            font-weight: 800;
        }
        .hero-illustration {
            max-width: 260px;
            width: 100%;
        }
        .subject-card {
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .subject-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 45px rgba(79, 141, 253, 0.16);
        }
        .journey-card {
            height: 100%;
            border-radius: 28px;
            padding: 1.75rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .journey-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 45px rgba(79, 141, 253, 0.16);
        }
        .journey-card::after {
            content: "";
            position: absolute;
            inset: auto -35px -45px auto;
            width: 140px;
            height: 140px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.4), transparent 70%);
            pointer-events: none;
        }
        .subject-theme-badge {
            color: #fff;
            border: 0;
        }
        .subject-theme-english {
            background: linear-gradient(135deg, #4f8dfd, #6cc7ff);
        }
        .subject-theme-portuguese {
            background: linear-gradient(135deg, #ff7eb6, #ff9f7d);
        }
        .subject-theme-math {
            background: linear-gradient(135deg, #47c972, #8ddf73);
        }
        .subject-theme-neutral {
            background: linear-gradient(135deg, #94a3b8, #64748b);
        }
        .difficulty-seal {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.5rem 0.95rem;
            border-radius: 999px;
            font-weight: 800;
            color: #fff;
            font-size: 0.9rem;
        }
        .difficulty-easy {
            background: linear-gradient(135deg, #47c972, #8ddf73);
        }
        .difficulty-normal {
            background: linear-gradient(135deg, #ffb703, #fb8500);
        }
        .difficulty-hard {
            background: linear-gradient(135deg, #ff6b6b, #ff4d8d);
        }
        .subject-icon {
            width: 72px;
            height: 72px;
            border-radius: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 1rem;
        }
        .accent-blue { background: linear-gradient(135deg, #4f8dfd, #6cc7ff); }
        .accent-pink { background: linear-gradient(135deg, #ff7eb6, #ff9f7d); }
        .accent-green { background: linear-gradient(135deg, #47c972, #8ddf73); }
        .utility-card {
            border-radius: 24px;
            padding: 1.25rem;
            height: 100%;
        }
        .feature-stat {
            border-radius: 24px;
            color: #fff;
            padding: 1.5rem;
            height: 100%;
            box-shadow: 0 18px 34px rgba(33, 53, 71, 0.12);
        }
        .feature-stat h3,
        .feature-stat p,
        .feature-stat h4 {
            color: inherit;
        }
        .feature-stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            margin-bottom: 0.85rem;
            font-size: 1.1rem;
        }
        .feature-stat-blue {
            background: linear-gradient(135deg, #4f8dfd, #6cc7ff);
        }
        .feature-stat-pink {
            background: linear-gradient(135deg, #ff7eb6, #ff9f7d);
        }
        .feature-stat-green {
            background: linear-gradient(135deg, #47c972, #8ddf73);
        }
        .feature-stat-gold {
            background: linear-gradient(135deg, #ffb703, #fb8500);
        }
        .feature-stat-silver {
            background: linear-gradient(135deg, #94a3b8, #cbd5e1);
            color: #213547;
        }
        .feature-stat-bronze {
            background: linear-gradient(135deg, #d97706, #f4a261);
        }
        .utility-card i {
            width: 56px;
            height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            color: #fff;
            margin-bottom: 0.85rem;
        }
        .auth-hero,
        .quiz-hero {
            position: relative;
            overflow: hidden;
        }
        .auth-hero::before,
        .quiz-hero::before {
            content: "";
            position: absolute;
            top: -90px;
            right: -40px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(255, 209, 102, 0.35), transparent 70%);
        }
        .auth-side {
            background: linear-gradient(160deg, rgba(79, 141, 253, 0.09), rgba(255, 143, 171, 0.12));
            border-radius: 24px;
            padding: 1.5rem;
            height: 100%;
        }
        .auth-side img {
            max-width: 220px;
        }
        .form-control,
        .form-select {
            border-radius: 16px;
            padding: 0.8rem 1rem;
            border: 1px solid rgba(79, 141, 253, 0.18);
            box-shadow: none;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: rgba(79, 141, 253, 0.6);
            box-shadow: 0 0 0 0.25rem rgba(79, 141, 253, 0.12);
        }
        .form-check {
            height: 100%;
        }
        .form-check-input {
            display: none;
        }
        .fas {
            filter: drop-shadow(1px 1px 2px rgba(0,0,0,0.2));
        }

        .form-check-input:checked + .form-check-label {
            background: linear-gradient(135deg, rgba(79, 141, 253, 0.15), rgba(255, 143, 171, 0.18));
            border-color: var(--kid-blue);
            color: var(--kid-ink);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }
        .form-check-label {
            min-height: 100%;
            background: rgba(255, 255, 255, 0.85);
            transition: all 0.18s ease;
        }
        .form-check-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(79, 141, 253, 0.12);
        }
        .subject-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 1rem;
            border-radius: 999px;
            font-weight: 800;
            color: #fff;
        }
        .list-card {
            border-radius: 24px;
            padding: 1.25rem;
            height: 100%;
        }
        .soft-panel {
            background: linear-gradient(180deg, rgba(255,255,255,0.86), rgba(255,255,255,0.96));
            border-radius: 24px;
            padding: 1.5rem;
        }
        .podium-card {
            position: relative;
            overflow: hidden;
            text-align: center;
            padding: 1.5rem;
            border-radius: 24px;
            height: 100%;
            animation: floatCard 5.5s ease-in-out infinite;
        }
        .podium-rank {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #fff;
            margin-bottom: 0.85rem;
        }
        .podium-gold { background: linear-gradient(135deg, #ffb703, #fb8500); }
        .podium-silver { background: linear-gradient(135deg, #94a3b8, #cbd5e1); color: #213547; }
        .podium-bronze { background: linear-gradient(135deg, #d97706, #f4a261); }
        .podium-trophy {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin: 0.85rem auto 1rem;
            box-shadow: 0 12px 24px rgba(33, 53, 71, 0.14);
        }
        .avatar-shell {
            width: 168px;
            height: 168px;
            border-radius: 44px;
            background: linear-gradient(135deg, rgba(79, 141, 253, 0.15), rgba(255, 143, 171, 0.18));
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: inset 0 0 0 10px rgba(255,255,255,0.6);
        }
        .avatar-shell img {
            width: 144px;
            height: 144px;
            object-fit: cover;
            border-radius: 36px;
        }
        .profile-quick-stat {
            border-radius: 22px;
            padding: 1rem 1.1rem;
            background: rgba(255,255,255,0.8);
            border: 1px solid rgba(79, 141, 253, 0.12);
            text-align: center;
        }
        .avatar-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #fff;
            font-family: 'Baloo 2', cursive;
            font-weight: 700;
            box-shadow: 0 16px 28px rgba(33, 53, 71, 0.16);
            background: linear-gradient(135deg, #4f8dfd, #6cc7ff);
        }
        .avatar-badge span {
            line-height: 1;
        }
        .avatar-badge-sm {
            width: 42px;
            height: 42px;
            font-size: 1rem;
        }
        .avatar-badge-md {
            width: 72px;
            height: 72px;
            font-size: 1.65rem;
        }
        .avatar-badge-lg {
            width: 92px;
            height: 92px;
            font-size: 2rem;
        }
        .avatar-rank-gold {
            background: linear-gradient(135deg, #ffb703, #fb8500);
        }
        .avatar-rank-silver {
            background: linear-gradient(135deg, #94a3b8, #cbd5e1);
            color: #213547;
        }
        .avatar-rank-bronze {
            background: linear-gradient(135deg, #d97706, #f4a261);
        }
        .avatar-rank-neutral {
            background: linear-gradient(135deg, #4f8dfd, #6cc7ff);
        }
        .avatar-trophy {
            position: absolute;
            right: -2px;
            bottom: -4px;
            width: 30px;
            height: 30px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: var(--kid-orange);
            border: 3px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 8px 16px rgba(33, 53, 71, 0.14);
            font-size: 0.85rem;
        }
        .subject-stage-hero {
            position: relative;
            overflow: hidden;
        }
        .subject-stage-hero::before {
            content: "";
            position: absolute;
            inset: auto auto -60px -50px;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.35), transparent 70%);
            pointer-events: none;
        }
        .micro-float {
            animation: floatCard 4.8s ease-in-out infinite;
        }
        .micro-delay-2 {
            animation-delay: 0.8s;
        }
        .micro-delay-3 {
            animation-delay: 1.6s;
        }
        @keyframes floatCard {
            0%,
            100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-6px);
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .podium-card,
            .micro-float {
                animation: none;
            }
        }
        @media (max-width: 991px) {
            .navbar .container {
                gap: 1rem;
            }
        }
        @media (max-width: 767px) {
            .navbar .container {
                border-radius: 18px;
            }
            .hero-illustration,
            .auth-side img {
                max-width: 180px;
            }
            .card {
                border-radius: 22px;
            }
        }
    </style>
</head>
<body>
    @php
        $headerSubjects = \App\Models\Subject::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($subject) {
                return $subject->name;
            });
    @endphp
    <nav class="navbar navbar-expand-lg navbar-dark bg-transparent mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <a class="navbar-brand fw-bold d-block" href="/">
                    <i class="fas fa-book-open me-2"></i>Marthina Learning
                </a>
                @if($headerSubjects->isNotEmpty())
                    <div class="d-flex gap-2 flex-wrap mt-2">
                        @foreach($headerSubjects as $headerSubjectLabel)
                            <span class="badge bg-light text-dark border">{{ $headerSubjectLabel }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            @php
                $guestMetrics = session('guest_metrics', ['score' => 0]);

                if(session('is_guest')) {
                    $globalScore = (int) ($guestMetrics['score'] ?? 0);
                } elseif(session('user_id')) {
                    $globalScore = \App\Models\Score::where('user_id', session('user_id'))->sum('score');
                } else {
                    $globalScore = \App\Models\Score::sum('score');
                }
            @endphp
            <div class="d-flex align-items-center gap-3">
                <div class="text-white fw-bold">
                    <i class="fas fa-star me-1"></i>Score: {{ $globalScore }}
                    @if(session('user_name'))
                        &nbsp;|&nbsp;{{ session('user_name') }}
                    @endif
                    @if(session('is_guest'))
                        &nbsp;|&nbsp;<span class="badge bg-light text-dark">Visitante</span>
                    @endif
                </div>

                @if(session('user_id') || session('is_guest'))
                    @if(session('user_id'))
                        <a href="/profile" class="btn btn-sm btn-light">Perfil</a>
                    @endif
                    @if(session('is_admin'))
                        <a href="/admin" class="btn btn-sm btn-light">Admin</a>
                    @endif
                    <a href="/logout" class="btn btn-sm btn-outline-light">Sair</a>
                @else
                    <div class="d-flex gap-2">
                        <a href="/login" class="btn btn-sm btn-outline-light">Entrar</a>
                        <a href="/register" class="btn btn-sm btn-light">Cadastrar</a>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <div class="container app-page">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
    @livewireScripts
</body>
</html>

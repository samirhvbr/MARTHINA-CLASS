<?php

use Illuminate\Support\Facades\Route;
use App\Models\Word;
use App\Models\Category;
use App\Models\AdminUserAction;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Score;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

$guestSessionKeys = [
    'is_guest',
    'is_admin',
    'user_name',
    'user_email',
    'guest_browser_token',
    'guest_metrics',
    'guest_quiz_records',
];

$clearGuestSession = function () use ($guestSessionKeys) {
    session()->forget($guestSessionKeys);
    Cookie::queue(Cookie::forget('guest_browser_session'));
};

$storeAuthenticatedUser = function (Request $request, User $user) use ($clearGuestSession) {
    $clearGuestSession();
    Auth::login($user);
    $request->session()->regenerate();
    $request->session()->put([
        'user_id' => $user->id,
        'is_admin' => $user->isAdmin(),
        'user_name' => $user->displayName(),
        'user_email' => $user->email,
    ]);
};

$clearAuthenticatedUser = function (Request $request) use ($clearGuestSession) {
    Auth::logout();
    $clearGuestSession();
    $request->session()->forget(['user_id', 'user_name', 'user_email']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();
};

$startGuestSession = function (Request $request) use ($clearGuestSession) {
    Auth::logout();
    $clearGuestSession();

    $request->session()->regenerate();

    $token = Str::random(40);

    config(['session.expire_on_close' => true]);

    $request->session()->put([
        'is_guest' => true,
        'is_admin' => false,
        'user_name' => 'Visitante',
        'user_email' => 'Sessao temporaria',
        'guest_browser_token' => $token,
        'guest_metrics' => [
            'score' => 0,
            'xp' => 0,
            'trophies' => [
                'gold' => 0,
                'silver' => 0,
                'bronze' => 0,
            ],
        ],
        'guest_quiz_records' => [],
    ]);

    Cookie::queue(Cookie::make('guest_browser_session', $token, 0));
};

$getGuestMetrics = function () {
    return session('guest_metrics', [
        'score' => 0,
        'xp' => 0,
        'trophies' => [
            'gold' => 0,
            'silver' => 0,
            'bronze' => 0,
        ],
    ]);
};

$getGuestCategoryRecords = function (int $categoryId) {
    return session('guest_quiz_records.' . $categoryId, []);
};

$recordGuestAttempt = function (int $categoryId, array $attempt) use ($getGuestMetrics) {
    $records = session('guest_quiz_records', []);
    $records[$categoryId] = array_values($records[$categoryId] ?? []);
    $records[$categoryId][] = $attempt;

    $metrics = $getGuestMetrics();
    $metrics['score'] += (int) ($attempt['score'] ?? 0);
    $metrics['xp'] += (int) ($attempt['xp'] ?? 0);

    session([
        'guest_quiz_records' => $records,
        'guest_metrics' => $metrics,
    ]);
};

$incrementGuestTrophy = function (string $trophy) use ($getGuestMetrics) {
    if (!in_array($trophy, ['gold', 'silver', 'bronze'], true)) {
        return;
    }

    $metrics = $getGuestMetrics();
    $metrics['trophies'][$trophy] = (int) ($metrics['trophies'][$trophy] ?? 0) + 1;

    session(['guest_metrics' => $metrics]);
};

$resetGuestCategoryProgress = function (int $categoryId) use ($getGuestMetrics) {
    $records = session('guest_quiz_records', []);
    $categoryRecords = $records[$categoryId] ?? [];

    if ($categoryRecords !== []) {
        $metrics = $getGuestMetrics();
        $metrics['score'] -= array_sum(array_map(fn ($record) => (int) ($record['score'] ?? 0), $categoryRecords));
        $metrics['xp'] -= array_sum(array_map(fn ($record) => (int) ($record['xp'] ?? 0), $categoryRecords));
        $metrics['score'] = max(0, (int) $metrics['score']);
        $metrics['xp'] = max(0, (int) $metrics['xp']);
        session(['guest_metrics' => $metrics]);
    }

    unset($records[$categoryId]);
    session(['guest_quiz_records' => $records]);
    session()->forget('quiz_done_' . $categoryId);
};

$syncAuthenticatedSession = function () use ($clearGuestSession) {
    if (session('is_guest')) {
        $guestToken = (string) session('guest_browser_token');

        if ($guestToken === '' || request()->cookie('guest_browser_session') !== $guestToken) {
            $clearGuestSession();
        } else {
            config(['session.expire_on_close' => true]);
        }
    }

    $user = Auth::user();

    if ($user instanceof User && !session()->has('user_id')) {
        session([
            'user_id' => $user->id,
            'is_admin' => $user->isAdmin(),
            'user_name' => $user->displayName(),
            'user_email' => $user->email,
        ]);
    }
};

$hasAuthenticatedUser = function () use ($syncAuthenticatedSession) {
    $syncAuthenticatedSession();

    return Auth::check() || session()->has('user_id') || session('is_guest');
};

$hasAdminAccess = function () use ($syncAuthenticatedSession) {
    $syncAuthenticatedSession();

    $user = Auth::user();

    return $user instanceof User && $user->isAdmin();
};

$normalizeAdminAlternatives = function (?Question $question = null, array $oldAlternatives = []) {
    if ($oldAlternatives !== []) {
        $alternatives = array_values($oldAlternatives);
        return array_pad(array_slice($alternatives, 0, 8), 8, '');
    }

    if (!$question) {
        return array_fill(0, 8, '');
    }

    $correct = $question->options->firstWhere('is_correct', true);
    $wrong = $question->options
        ->filter(fn (QuestionOption $option) => !$option->is_correct)
        ->sortBy('sort_order')
        ->pluck('option_text')
        ->values()
        ->all();

    $alternatives = [];
    $alternatives[] = $correct?->option_text ?? '';
    $alternatives = array_merge($alternatives, $wrong);

    return array_pad(array_slice($alternatives, 0, 8), 8, '');
};

$logAdminUserAction = function (User $adminUser, User $targetUser, string $action, string $justification) {
    AdminUserAction::create([
        'admin_user_id' => $adminUser->id,
        'target_user_id' => $targetUser->id,
        'action' => $action,
        'justification' => $justification,
    ]);
};

$buildAdminQuery = function (Request $request) {
    $params = array_filter([
        'subject_id' => (string) $request->input('subject_id', $request->query('subject_id', '')),
        'category_id' => (string) $request->input('category_id', $request->query('category_id', '')),
        'user_search' => trim((string) $request->input('user_search', $request->query('user_search', ''))),
        'user_status' => trim((string) $request->input('user_status', $request->query('user_status', ''))),
    ], fn ($value) => $value !== '');

    return http_build_query($params);
};

$buildAdminUrl = function (string $path, Request $request) use ($buildAdminQuery) {
    $query = $buildAdminQuery($request);

    if ($query === '') {
        return $path;
    }

    return $path . '?' . $query;
};

$formatSubjectLabel = function (?Subject $subject): string {
    if (!$subject) {
        return 'Geral';
    }

    return $subject->name;
};

$buildPublicSubjectLegend = function () use ($formatSubjectLabel) {
    return Subject::where('is_active', true)
        ->orderBy('name')
        ->get()
        ->map(function (Subject $subject) use ($formatSubjectLabel) {
            return [
                'id' => $subject->id,
                'label' => $formatSubjectLabel($subject),
                'icon' => $subject->icon,
            ];
        });
};

$formatSubjectAdminLabel = function (?Subject $subject): string {
    if (!$subject) {
        return '';
    }

    return trim($subject->slug . ' ' . $subject->name);
};

$renderAdminDashboard = function (Request $request, ?Question $editingQuestion = null, ?User $editingUser = null) use ($hasAdminAccess, $normalizeAdminAlternatives, $formatSubjectAdminLabel) {
    if (!$hasAdminAccess()) {
        return redirect('/')->with('error', 'Acesso restrito ao administrador.');
    }

    $subjects = Subject::where('is_active', true)
        ->orderBy('name')
        ->get();

    $categories = Category::with('subject')
        ->where('quiz_type', Category::QUIZ_TYPE_MULTIPLE_CHOICE)
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    $subjectAdminLabels = [];
    foreach ($subjects as $subject) {
        $subjectAdminLabels[$subject->id] = $formatSubjectAdminLabel($subject);
    }

    $categoryAdminLabels = [];
    foreach ($categories as $category) {
        $subjectLabel = $subjectAdminLabels[$category->subject_id] ?? $formatSubjectAdminLabel($category->subject);
        $categoryAdminLabels[$category->id] = trim($subjectLabel . ' - ' . $category->name, ' -');
    }

    $selectedSubjectId = (int) ($request->query('subject_id', old('subject_id', $editingQuestion?->category?->subject_id ?? ($subjects->first()->id ?? 0))));
    $selectedCategoryId = (int) ($request->query('category_id', old('category_id', $editingQuestion?->category_id ?? 0)));

    if ($selectedCategoryId === 0) {
        $selectedCategoryId = (int) ($categories->firstWhere('subject_id', $selectedSubjectId)?->id ?? ($categories->first()->id ?? 0));
    }

    if ($selectedSubjectId === 0) {
        $selectedSubjectId = (int) ($categories->firstWhere('id', $selectedCategoryId)?->subject_id ?? 0);
    }

    $questions = Question::with(['category.subject', 'options'])
        ->when($selectedCategoryId > 0, fn ($query) => $query->where('category_id', $selectedCategoryId))
        ->orderBy('updated_at', 'desc')
        ->get();

    $userSearch = trim((string) $request->query('user_search', ''));
    $userStatus = (string) $request->query('user_status', 'all');
    $users = User::withTrashed()
        ->withCount(['scores', 'quizResults'])
        ->when($userSearch !== '', function ($query) use ($userSearch) {
            $query->where(function ($subQuery) use ($userSearch) {
                $subQuery
                    ->where('name', 'like', '%' . $userSearch . '%')
                    ->orWhere('last_name', 'like', '%' . $userSearch . '%')
                    ->orWhere('email', 'like', '%' . $userSearch . '%')
                    ->orWhere('phone', 'like', '%' . $userSearch . '%');
            });
        })
        ->when($userStatus === 'active', fn ($query) => $query->whereNull('deleted_at')->where('is_active', true))
        ->when($userStatus === 'blocked', fn ($query) => $query->whereNull('deleted_at')->where('is_active', false))
        ->when($userStatus === 'deleted', fn ($query) => $query->onlyTrashed())
        ->orderByDesc('is_admin')
        ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END')
        ->orderByDesc('is_active')
        ->orderBy('name')
        ->get();

    $editingUserScores = $editingUser
        ? Score::with(['category', 'question', 'word'])
            ->where('user_id', $editingUser->id)
            ->latest()
            ->limit(15)
            ->get()
        : collect();

    $editingUserQuizResults = $editingUser
        ? \App\Models\QuizResult::with('category')
            ->where('user_id', $editingUser->id)
            ->latest()
            ->limit(15)
            ->get()
        : collect();

    $editingUserAdminActions = $editingUser
        ? AdminUserAction::with(['adminUser', 'targetUser'])
            ->where('target_user_id', $editingUser->id)
            ->latest()
            ->limit(20)
            ->get()
        : collect();

    $isEditingQuestion = false;
    $editingQuestionId = '';
    $editingQuestionPromptDefault = '';
    $editingQuestionSupportTextDefault = '';
    $editingQuestionExplanationDefault = '';
    $editingQuestionDifficultyDefault = 'easy';

    if ($editingQuestion) {
        $isEditingQuestion = true;
        $editingQuestionId = $editingQuestion->id;
        $editingQuestionPromptDefault = $editingQuestion->prompt;
        $editingQuestionSupportTextDefault = $editingQuestion->support_text;
        $editingQuestionExplanationDefault = $editingQuestion->explanation;
        $editingQuestionDifficultyDefault = $editingQuestion->difficulty;
    }

    $editingQuestionPrompt = old('prompt', $editingQuestionPromptDefault);
    $editingQuestionSupportText = old('support_text', $editingQuestionSupportTextDefault);
    $editingQuestionExplanation = old('explanation', $editingQuestionExplanationDefault);
    $editingQuestionDifficulty = old('difficulty', $editingQuestionDifficultyDefault);
    $questionJsonPayload = old('question_json', '');
    $questionJsonTemplate = implode("\n", [
        '{',
        '  "MATERIA": "ingles",',
        '  "CATEGORIA": "English Grammar",',
        '  "PERGUNTA": "Digite aqui a pergunta",',
        '  "TEXTO DE APOIO": "Texto opcional",',
        '  "EXPLICACAO": "Explique a resposta correta",',
        '  "RESPOSTA": "Alternativa correta",',
        '  "RESPOSTA ERRADA 1": "Alternativa errada 1",',
        '  "RESPOSTA ERRADA 2": "Alternativa errada 2",',
        '  "RESPOSTA ERRADA 3": "Alternativa errada 3",',
        '  "RESPOSTA ERRADA 4": "Alternativa errada 4",',
        '  "RESPOSTA ERRADA 5": "Alternativa errada 5",',
        '  "RESPOSTA ERRADA 6": "Alternativa errada 6",',
        '  "RESPOSTA ERRADA 7": "Alternativa errada 7"',
        '}',
    ]);
    $questionSubmitLabel = $isEditingQuestion ? 'Atualizar questao' : 'Salvar questao';

    $isEditingUser = false;
    $editingUserId = '';
    $editingUserNameDefault = '';
    $editingUserLastNameDefault = '';
    $editingUserEmailDefault = '';
    $editingUserPhoneDefault = '';
    $editingUserBioDefault = '';
    $editingUserIsAdminDefault = false;

    if ($editingUser) {
        $isEditingUser = true;
        $editingUserId = $editingUser->id;
        $editingUserNameDefault = $editingUser->name;
        $editingUserLastNameDefault = $editingUser->last_name;
        $editingUserEmailDefault = $editingUser->email;
        $editingUserPhoneDefault = $editingUser->phone;
        $editingUserBioDefault = $editingUser->bio;
        $editingUserIsAdminDefault = (bool) $editingUser->is_admin;
    }

    $editingUserName = old('name', $editingUserNameDefault);
    $editingUserLastName = old('last_name', $editingUserLastNameDefault);
    $editingUserEmail = old('email', $editingUserEmailDefault);
    $editingUserPhone = old('phone', $editingUserPhoneDefault);
    $editingUserBio = old('bio', $editingUserBioDefault);
    $editingUserIsAdmin = old('is_admin', $editingUserIsAdminDefault);
    $editingUserIsActive = old('is_active', true);
    $editingUserIsTrashed = false;
    $editingUserDisplayName = '';
    $editingUserAvatarPath = null;
    $editingUserStatusLabel = '';
    $editingUserStatusBadgeClass = 'bg-success';
    $editingUserRoleBadgeClass = 'bg-secondary';
    $editingUserRoleLabel = 'Aluno';
    $editingUserFormTitle = 'Criar usuario manualmente';
    $editingUserPasswordLabel = 'Senha inicial';
    $editingUserPasswordHelp = 'Obrigatoria para criar a conta manualmente.';
    $editingUserSubmitLabel = 'Criar usuario';
    $editingUserFieldsetDisabled = '';

    if ($editingUser) {
        $editingUserIsTrashed = $editingUser->trashed();
        $editingUserDisplayName = $editingUser->displayName();
        $editingUserAvatarPath = $editingUser->avatar_path;
        $editingUserStatusLabel = $editingUser->statusLabel();
        $editingUserFormTitle = 'Editar usuario';
        $editingUserPasswordLabel = 'Nova senha';
        $editingUserPasswordHelp = 'Preencha apenas se quiser redefinir a senha deste usuario.';
        $editingUserSubmitLabel = 'Salvar usuario';

        if ($editingUser->is_admin) {
            $editingUserRoleBadgeClass = 'bg-warning text-dark';
            $editingUserRoleLabel = 'Admin';
        }

        if ($editingUserIsTrashed) {
            $editingUserStatusBadgeClass = 'bg-dark';
            $editingUserFieldsetDisabled = 'disabled';
        } elseif (!$editingUser->is_active) {
            $editingUserStatusBadgeClass = 'bg-danger';
        }
    }

    $restoreJustification = old('restore_justification', '');
    $blockJustification = old('block_justification', '');
    $unblockJustification = old('unblock_justification', '');
    $deleteJustification = old('delete_justification', '');

    $categoryOptionsData = [];
    foreach ($categories as $categoryItem) {
        $categoryOptionsData[] = [
            'id' => $categoryItem->id,
            'subject_id' => $categoryItem->subject_id,
            'label' => $categoryAdminLabels[$categoryItem->id] ?? $categoryItem->name,
        ];
    }

    return view('admin.dashboard', [
        'flashMessage' => session('message'),
        'flashError' => session('error'),
        'subjects' => $subjects,
        'subjectAdminLabels' => $subjectAdminLabels,
        'categories' => $categories,
        'categoryAdminLabels' => $categoryAdminLabels,
        'difficultyLabels' => Question::difficultyLabels(),
        'selectedSubjectId' => $selectedSubjectId,
        'selectedCategoryId' => $selectedCategoryId,
        'questions' => $questions,
        'editingQuestion' => $editingQuestion,
        'alternatives' => $normalizeAdminAlternatives($editingQuestion, old('alternatives', [])),
        'users' => $users,
        'userSearch' => $userSearch,
        'userStatus' => $userStatus,
        'editingUser' => $editingUser,
        'editingUserScores' => $editingUserScores,
        'editingUserQuizResults' => $editingUserQuizResults,
        'editingUserAdminActions' => $editingUserAdminActions,
        'editingQuestionId' => $editingQuestionId,
        'editingQuestionPrompt' => $editingQuestionPrompt,
        'editingQuestionSupportText' => $editingQuestionSupportText,
        'editingQuestionExplanation' => $editingQuestionExplanation,
        'editingQuestionDifficulty' => $editingQuestionDifficulty,
        'questionJsonPayload' => $questionJsonPayload,
        'questionJsonTemplate' => $questionJsonTemplate,
        'questionSubmitLabel' => $questionSubmitLabel,
        'isEditingUser' => $isEditingUser,
        'editingUserId' => $editingUserId,
        'editingUserName' => $editingUserName,
        'editingUserLastName' => $editingUserLastName,
        'editingUserEmail' => $editingUserEmail,
        'editingUserPhone' => $editingUserPhone,
        'editingUserBio' => $editingUserBio,
        'editingUserIsAdmin' => $editingUserIsAdmin,
        'editingUserIsActive' => $editingUserIsActive,
        'editingUserIsTrashed' => $editingUserIsTrashed,
        'editingUserDisplayName' => $editingUserDisplayName,
        'editingUserAvatarPath' => $editingUserAvatarPath,
        'editingUserStatusLabel' => $editingUserStatusLabel,
        'editingUserStatusBadgeClass' => $editingUserStatusBadgeClass,
        'editingUserRoleBadgeClass' => $editingUserRoleBadgeClass,
        'editingUserRoleLabel' => $editingUserRoleLabel,
        'editingUserFormTitle' => $editingUserFormTitle,
        'editingUserPasswordLabel' => $editingUserPasswordLabel,
        'editingUserPasswordHelp' => $editingUserPasswordHelp,
        'editingUserSubmitLabel' => $editingUserSubmitLabel,
        'editingUserFieldsetDisabled' => $editingUserFieldsetDisabled,
        'restoreJustification' => $restoreJustification,
        'blockJustification' => $blockJustification,
        'unblockJustification' => $unblockJustification,
        'deleteJustification' => $deleteJustification,
        'categoryOptionsData' => $categoryOptionsData,
    ]);
};

$buildLeaderboard = function () {
    return User::query()
        ->leftJoin('scores', 'users.id', '=', 'scores.user_id')
        ->select('users.id', 'users.name', 'users.email')
        ->selectRaw('COALESCE(SUM(scores.score), 0) as total_score')
        ->selectRaw('COALESCE(SUM(scores.xp), 0) as total_xp')
        ->selectRaw('COALESCE(SUM(CASE WHEN scores.correct = 1 THEN 1 ELSE 0 END), 0) as total_correct')
        ->groupBy('users.id', 'users.name', 'users.email')
        ->orderByDesc('total_score')
        ->orderByDesc('total_xp')
        ->orderBy('users.name')
        ->get()
        ->values()
        ->map(function ($entry, $index) {
            $entry->rank = $index + 1;

            return $entry;
        });
};

$getAuthenticatedAccount = function () use ($syncAuthenticatedSession) {
    $syncAuthenticatedSession();

    $user = Auth::user();

    return $user instanceof User ? $user : null;
};

// Página inicial
Route::get('/', function () use ($hasAuthenticatedUser, $formatSubjectLabel) {
    if (!$hasAuthenticatedUser()) {
        return redirect('/login');
    }

    $subjectOrder = [
        'prt_' => 1,
        'eng_' => 2,
        'mat_' => 3,
    ];

    $subjects = Subject::where('is_active', true)
        ->get()
        ->sortBy(function (Subject $subject) use ($subjectOrder) {
            return $subjectOrder[$subject->slug] ?? 99;
        })
        ->values();

    $subjectCards = $subjects->map(function (Subject $subject) use ($formatSubjectLabel) {
        return [
            'id' => $subject->id,
            'name' => $subject->name,
            'label' => $formatSubjectLabel($subject),
            'description' => $subject->description,
            'icon' => $subject->icon ?: 'book',
            'url' => '/subjects/' . $subject->id,
        ];
    });

    return view('home', [
        'subjectCards' => $subjectCards,
    ]);
});

Route::get('/subjects/{subject}', function (int $subject) use ($hasAuthenticatedUser, $formatSubjectLabel) {
    if (!$hasAuthenticatedUser()) {
        return redirect('/login');
    }

    $subject = Subject::where('is_active', true)->find($subject);

    if (!$subject) {
        return redirect('/')->with('error', 'Materia nao encontrada.');
    }

    $subjectLabel = $formatSubjectLabel($subject);
    $activityCount = Category::where('subject_id', $subject->id)
        ->where('is_active', true)
        ->count();
    $vocabularyCount = Category::where('subject_id', $subject->id)
        ->where('is_active', true)
        ->where('quiz_type', Category::QUIZ_TYPE_VOCABULARY)
        ->whereHas('words')
        ->count();

    return view('subject', [
        'subject' => $subject,
        'subjectLabel' => $subjectLabel,
        'activityUrl' => '/lessons?subject_id=' . $subject->id,
        'activityCount' => $activityCount,
        'vocabularyUrl' => route('vocabulary', ['subject_id' => $subject->id]),
        'vocabularyCount' => $vocabularyCount,
        'hasVocabulary' => $vocabularyCount > 0,
    ]);
});

Route::get('/login', function () use ($hasAuthenticatedUser) {
    if ($hasAuthenticatedUser()) {
        return redirect('/');
    }

    return view('auth.login', [
        'flashMessage' => session('message'),
        'flashError' => session('error'),
        'formEmail' => old('email', ''),
    ]);
});

Route::post('/login', function (Request $request) use ($storeAuthenticatedUser) {
    if (trim((string) $request->input('company')) !== '') {
        return redirect('/login')->with('error', 'Nao foi possivel validar o envio.');
    }

    $email = strtolower(trim((string) $request->input('email')));
    $password = (string) $request->input('password');

    if ($email === '' || $password === '') {
        return redirect('/login')
            ->with('error', 'Preencha e-mail e senha.')
            ->withInput($request->except('password'));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return redirect('/login')
            ->with('error', 'Digite um e-mail valido.')
            ->withInput($request->except('password'));
    }

    $matchedUser = User::withTrashed()->where('email', $email)->first();

    if ($matchedUser && $matchedUser->trashed()) {
        return redirect('/login')
            ->with('error', 'Sua conta foi desativada. Fale com o administrador para restaurar o acesso.')
            ->withInput($request->except('password'));
    }

    if ($matchedUser && !$matchedUser->isActive()) {
        return redirect('/login')
            ->with('error', 'Sua conta esta bloqueada. Fale com o administrador.')
            ->withInput($request->except('password'));
    }

    if (!Auth::attempt(['email' => $email, 'password' => $password])) {
        return redirect('/login')
            ->with('error', 'E-mail ou senha invalidos.')
            ->withInput($request->except('password'));
    }

    $user = Auth::user();

    if (!$user instanceof User) {
        Auth::logout();

        return redirect('/login')
            ->with('error', 'Nao foi possivel concluir o login. Tente novamente.');
    }

    $storeAuthenticatedUser($request, $user);

    return redirect('/')->with('message', 'Login realizado com sucesso.');
});

Route::post('/guest-login', function (Request $request) use ($startGuestSession) {
    if (trim((string) $request->input('company')) !== '') {
        return redirect('/login')->with('error', 'Nao foi possivel validar o envio.');
    }

    $startGuestSession($request);

    return redirect('/')->with('message', 'Acesso visitante liberado. Seu progresso sera mantido ate voce fechar o navegador.');
});

Route::get('/forgot-password', function () use ($hasAuthenticatedUser) {
    if ($hasAuthenticatedUser()) {
        return redirect('/');
    }

    return view('auth.forgot-password', [
        'flashMessage' => session('message'),
        'flashError' => session('error'),
        'formEmail' => old('email', ''),
        'usesLogMailer' => config('mail.default') === 'log',
    ]);
});

Route::post('/forgot-password', function (Request $request) {
    if (trim((string) $request->input('company')) !== '') {
        return redirect('/forgot-password')->with('error', 'Nao foi possivel validar o envio.');
    }

    $email = strtolower(trim((string) $request->input('email')));

    if ($email === '') {
        return redirect('/forgot-password')
            ->with('error', 'Informe o e-mail cadastrado.')
            ->withInput($request->except('company'));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return redirect('/forgot-password')
            ->with('error', 'Digite um e-mail valido.')
            ->withInput($request->except('company'));
    }

    $user = User::where('email', $email)->first();

    if ($user) {
        $plainToken = Str::random(64);
        $resetTable = (string) config('auth.passwords.users.table', 'password_reset_tokens');

        DB::table($resetTable)->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        $resetUrl = url('/reset-password/' . $plainToken . '?email=' . urlencode($email));

        Mail::raw(
            "Ola, {$user->name}!\n\nRecebemos um pedido para redefinir sua senha no Marthina.\n\nAbra este link para cadastrar uma nova senha:\n{$resetUrl}\n\nSe voce nao solicitou a troca, ignore esta mensagem.",
            function ($message) use ($email) {
                $message->to($email)->subject('Redefinicao de senha - Marthina');
            }
        );
    }

    return redirect('/forgot-password')->with(
        'message',
        'Se o e-mail estiver cadastrado, enviamos as instrucoes para redefinir a senha.'
    );
});

Route::get('/reset-password/{token}', function (string $token, Request $request) {
    if ($token === '') {
        return redirect('/forgot-password')->with('error', 'Link de redefinicao invalido.');
    }

    $email = strtolower(trim((string) $request->query('email')));

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return redirect('/forgot-password')->with('error', 'Link de redefinicao invalido ou incompleto.');
    }

    return view('auth.reset-password', [
        'email' => $email,
        'token' => $token,
        'flashError' => session('error'),
    ]);
});

Route::post('/reset-password', function (Request $request) use ($storeAuthenticatedUser, $clearAuthenticatedUser) {
    if (trim((string) $request->input('company')) !== '') {
        return redirect('/forgot-password')->with('error', 'Nao foi possivel validar o envio.');
    }

    $email = strtolower(trim((string) $request->input('email')));
    $token = (string) $request->input('token');
    $password = (string) $request->input('password');
    $passwordConfirmation = (string) $request->input('password_confirmation');

    if ($email === '' || $token === '' || $password === '' || $passwordConfirmation === '') {
        return redirect('/reset-password/' . $token . '?email=' . urlencode($email))
            ->with('error', 'Preencha todos os campos.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return redirect('/forgot-password')->with('error', 'Digite um e-mail valido.');
    }

    if (strlen($password) < 8) {
        return redirect('/reset-password/' . $token . '?email=' . urlencode($email))
            ->with('error', 'A nova senha precisa ter pelo menos 8 caracteres.');
    }

    if ($password !== $passwordConfirmation) {
        return redirect('/reset-password/' . $token . '?email=' . urlencode($email))
            ->with('error', 'A confirmacao da senha nao confere.');
    }

    $resetTable = (string) config('auth.passwords.users.table', 'password_reset_tokens');
    $resetEntry = DB::table($resetTable)->where('email', $email)->first();

    if (!$resetEntry || !Hash::check($token, $resetEntry->token)) {
        return redirect('/forgot-password')->with('error', 'O link de redefinicao e invalido ou ja expirou.');
    }

    $expiresAt = Carbon::parse($resetEntry->created_at)->addMinutes((int) config('auth.passwords.users.expire', 60));

    if (now()->greaterThan($expiresAt)) {
        DB::table($resetTable)->where('email', $email)->delete();

        return redirect('/forgot-password')->with('error', 'O link de redefinicao expirou. Solicite um novo envio.');
    }

    $user = User::where('email', $email)->first();

    if (!$user) {
        DB::table($resetTable)->where('email', $email)->delete();

        return redirect('/forgot-password')->with('error', 'Nao encontramos uma conta para este link.');
    }

    $user->password = $password;
    $user->save();

    DB::table($resetTable)->where('email', $email)->delete();

    $sessionTable = (string) config('session.table', 'sessions');
    DB::table($sessionTable)->where('user_id', $user->id)->delete();

    $clearAuthenticatedUser($request);
    $storeAuthenticatedUser($request, $user);

    return redirect('/')->with('message', 'Senha redefinida com sucesso.');
});

Route::get('/register', function () use ($hasAuthenticatedUser) {
    if ($hasAuthenticatedUser()) {
        return redirect('/');
    }

    $firstNumber = random_int(1, 9);
    $secondNumber = random_int(1, 9);

    session([
        'register_form_started_at' => time(),
        'register_human_answer' => $firstNumber + $secondNumber,
    ]);

    return view('auth.register', [
        'firstNumber' => $firstNumber,
        'secondNumber' => $secondNumber,
        'flashError' => session('error'),
        'formName' => old('name', ''),
        'formEmail' => old('email', ''),
    ]);
});

Route::post('/register', function (Request $request) use ($storeAuthenticatedUser) {
    $honeypot = trim((string) $request->input('company'));
    $startedAt = (int) session('register_form_started_at', 0);
    $expectedAnswer = (int) session('register_human_answer', -1);

    session()->forget(['register_form_started_at', 'register_human_answer']);

    if ($honeypot !== '' || !$startedAt || (time() - $startedAt) < 3 || (int) $request->input('human_check') !== $expectedAnswer) {
        return redirect('/register')
            ->with('error', 'Nao foi possivel validar o cadastro. Tente novamente.')
            ->withInput($request->except(['company', 'human_check']));
    }

    $name = trim((string) $request->input('name'));
    $email = strtolower(trim((string) $request->input('email')));

    if ($name === '' || $email === '') {
        return redirect('/register')
            ->with('error', 'Preencha nome e e-mail.')
            ->withInput($request->except(['company', 'human_check']));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return redirect('/register')
            ->with('error', 'Digite um e-mail valido.')
            ->withInput($request->except(['company', 'human_check']));
    }

    if (User::where('email', $email)->exists()) {
        return redirect('/login')
            ->with('error', 'Ja existe uma conta com este e-mail.');
    }

    $generatedPassword = Str::upper(Str::random(10));

    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => $generatedPassword,
    ]);

    $storeAuthenticatedUser($request, $user);

    return redirect('/')
        ->with('message', 'Cadastro concluido. Sua senha inicial e: ' . $generatedPassword . '. Guarde essa senha para entrar depois.');
});

Route::get('/logout', function (Request $request) use ($clearAuthenticatedUser) {
    $clearAuthenticatedUser($request);

    return redirect('/login');
});

Route::get('/profile', function () use ($getAuthenticatedAccount) {
    if (session('is_guest')) {
        return redirect('/')->with('error', 'Visitantes nao podem editar perfil. Crie uma conta ou faca login.');
    }

    $user = $getAuthenticatedAccount();

    if (!$user) {
        return redirect('/login');
    }

    return view('profile', ['user' => $user]);
});

Route::post('/profile', function (Request $request) use ($getAuthenticatedAccount) {
    if (session('is_guest')) {
        return redirect('/')->with('error', 'Visitantes nao podem editar perfil.');
    }

    $user = $getAuthenticatedAccount();

    if (!$user) {
        return redirect('/login');
    }

    $firstName = trim((string) $request->input('name'));
    $lastName = trim((string) $request->input('last_name'));
    $email = strtolower(trim((string) $request->input('email')));
    $phone = trim((string) $request->input('phone'));
    $bio = trim((string) $request->input('bio'));

    if ($firstName === '' || $email === '') {
        return redirect('/profile')
            ->with('error', 'Nome e e-mail sao obrigatorios.')
            ->withInput();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return redirect('/profile')
            ->with('error', 'Digite um e-mail valido.')
            ->withInput();
    }

    $emailExists = User::where('email', $email)
        ->where('id', '!=', $user->id)
        ->exists();

    if ($emailExists) {
        return redirect('/profile')
            ->with('error', 'Este e-mail ja esta em uso por outra conta.')
            ->withInput();
    }

    if ($request->hasFile('avatar')) {
        $avatar = $request->file('avatar');

        if (!$avatar->isValid()) {
            return redirect('/profile')
                ->with('error', 'Nao foi possivel enviar a foto de perfil.')
                ->withInput();
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($avatar->getMimeType(), $allowedMimeTypes, true)) {
            return redirect('/profile')
                ->with('error', 'Envie uma imagem JPG, PNG ou WEBP.')
                ->withInput();
        }

        if ($avatar->getSize() > 2 * 1024 * 1024) {
            return redirect('/profile')
                ->with('error', 'A foto deve ter no maximo 2 MB.')
                ->withInput();
        }

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = $avatar->store('avatars', 'public');
    }

    if ($request->boolean('remove_avatar') && $user->avatar_path) {
        if (Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = null;
    }

    $user->name = $firstName;
    $user->last_name = $lastName !== '' ? $lastName : null;
    $user->email = $email;
    $user->phone = $phone !== '' ? $phone : null;
    $user->bio = $bio !== '' ? $bio : null;
    $user->save();

    session([
        'user_name' => $user->displayName(),
        'user_email' => $user->email,
    ]);

    return redirect('/profile')->with('message', 'Perfil atualizado com sucesso.');
});

Route::post('/profile/password', function (Request $request) use ($getAuthenticatedAccount) {
    if (session('is_guest')) {
        return redirect('/')->with('error', 'Visitantes nao podem alterar senha.');
    }

    $user = $getAuthenticatedAccount();

    if (!$user) {
        return redirect('/login');
    }

    $currentPassword = (string) $request->input('current_password');
    $newPassword = (string) $request->input('password');
    $passwordConfirmation = (string) $request->input('password_confirmation');

    if ($currentPassword === '' || $newPassword === '' || $passwordConfirmation === '') {
        return redirect('/profile')->with('error', 'Preencha os tres campos de senha.');
    }

    if (!Hash::check($currentPassword, $user->password)) {
        return redirect('/profile')->with('error', 'A senha atual esta incorreta.');
    }

    if (strlen($newPassword) < 8) {
        return redirect('/profile')->with('error', 'A nova senha precisa ter pelo menos 8 caracteres.');
    }

    if ($newPassword !== $passwordConfirmation) {
        return redirect('/profile')->with('error', 'A confirmacao da nova senha nao confere.');
    }

    $user->password = $newPassword;
    $user->save();

    return redirect('/profile')->with('message', 'Senha alterada com sucesso.');
});

Route::get('/profile/avatar/{user}', function (User $user) {
    if (!$user->avatar_path || !Storage::disk('public')->exists($user->avatar_path)) {
        abort(404);
    }

    return Storage::disk('public')->response($user->avatar_path);
});

Route::get('/admin', function (Request $request) use ($renderAdminDashboard) {
    return $renderAdminDashboard($request);
});

Route::get('/admin/questions/{question}/edit', function (Request $request, Question $question) use ($renderAdminDashboard) {
    $question->load(['category.subject', 'options']);

    return $renderAdminDashboard($request, $question);
});

Route::get('/admin/users/{user}/edit', function (Request $request, int $user) use ($renderAdminDashboard) {
    $editingUser = User::withTrashed()->findOrFail($user);

    return $renderAdminDashboard($request, null, $editingUser);
});

Route::post('/admin/users/restore', function (Request $request) use ($hasAdminAccess, $getAuthenticatedAccount, $logAdminUserAction, $buildAdminUrl) {
    if (!$hasAdminAccess()) {
        return redirect('/')->with('error', 'Acesso restrito ao administrador.');
    }

    $user = User::withTrashed()->find((int) $request->input('user_id'));
    $adminIndexUrl = $buildAdminUrl('/admin', $request);
    $editUserUrl = $buildAdminUrl('/admin/users/' . (int) $request->input('user_id') . '/edit', $request);

    if (!$user || !$user->trashed()) {
        return redirect($adminIndexUrl)->with('error', 'Usuario nao encontrado para restauracao.');
    }

    $justification = trim((string) $request->input('restore_justification'));

    if ($justification === '') {
        return redirect($editUserUrl)
            ->with('error', 'Informe a justificativa administrativa para restaurar a conta.')
            ->withInput();
    }

    $authenticatedAdmin = $getAuthenticatedAccount();

    if (!$authenticatedAdmin) {
        return redirect('/login')->with('error', 'Sua sessao expirou. Faca login novamente.');
    }

    $user->restore();
    $user->is_active = true;
    $user->blocked_at = null;
    $user->save();

    $logAdminUserAction($authenticatedAdmin, $user, AdminUserAction::ACTION_RESTORE, $justification);

    return redirect($buildAdminUrl('/admin/users/' . $user->id . '/edit', $request))
        ->with('message', 'Conta restaurada com sucesso.');
});

Route::post('/admin/users/block', function (Request $request) use ($hasAdminAccess, $getAuthenticatedAccount, $logAdminUserAction, $buildAdminUrl) {
    if (!$hasAdminAccess()) {
        return redirect('/')->with('error', 'Acesso restrito ao administrador.');
    }

    $user = User::withTrashed()->find((int) $request->input('user_id'));
    $adminIndexUrl = $buildAdminUrl('/admin', $request);
    $editUserUrl = $buildAdminUrl('/admin/users/' . (int) $request->input('user_id') . '/edit', $request);

    if (!$user || $user->trashed()) {
        return redirect($adminIndexUrl)->with('error', 'Usuario nao encontrado para bloqueio.');
    }

    $authenticatedAdmin = $getAuthenticatedAccount();

    if (!$authenticatedAdmin) {
        return redirect('/login')->with('error', 'Sua sessao expirou. Faca login novamente.');
    }

    if ($authenticatedAdmin->id === $user->id) {
        return redirect($editUserUrl)
            ->with('error', 'Voce nao pode bloquear a propria conta.');
    }

    if (!$user->is_active) {
        return redirect($editUserUrl)
            ->with('error', 'Esta conta ja esta bloqueada.');
    }

    $justification = trim((string) $request->input('block_justification'));

    if ($justification === '') {
        return redirect($editUserUrl)
            ->with('error', 'Informe a justificativa administrativa para bloquear a conta.')
            ->withInput();
    }

    $sessionTable = (string) config('session.table', 'sessions');
    DB::table($sessionTable)->where('user_id', $user->id)->delete();

    $user->is_active = false;
    $user->blocked_at = now();
    $user->save();

    $logAdminUserAction($authenticatedAdmin, $user, AdminUserAction::ACTION_BLOCK, $justification);

    return redirect($buildAdminUrl('/admin/users/' . $user->id . '/edit', $request))
        ->with('message', 'Conta bloqueada com sucesso.');
});

Route::post('/admin/users/unblock', function (Request $request) use ($hasAdminAccess, $getAuthenticatedAccount, $logAdminUserAction, $buildAdminUrl) {
    if (!$hasAdminAccess()) {
        return redirect('/')->with('error', 'Acesso restrito ao administrador.');
    }

    $user = User::withTrashed()->find((int) $request->input('user_id'));
    $adminIndexUrl = $buildAdminUrl('/admin', $request);
    $editUserUrl = $buildAdminUrl('/admin/users/' . (int) $request->input('user_id') . '/edit', $request);

    if (!$user || $user->trashed()) {
        return redirect($adminIndexUrl)->with('error', 'Usuario nao encontrado para desbloqueio.');
    }

    $authenticatedAdmin = $getAuthenticatedAccount();

    if (!$authenticatedAdmin) {
        return redirect('/login')->with('error', 'Sua sessao expirou. Faca login novamente.');
    }

    if (!$user->blocked_at && $user->is_active) {
        return redirect($editUserUrl)
            ->with('error', 'Esta conta ja esta ativa.');
    }

    $justification = trim((string) $request->input('unblock_justification'));

    if ($justification === '') {
        return redirect($editUserUrl)
            ->with('error', 'Informe a justificativa administrativa para desbloquear a conta.')
            ->withInput();
    }

    $user->is_active = true;
    $user->blocked_at = null;
    $user->save();

    $logAdminUserAction($authenticatedAdmin, $user, AdminUserAction::ACTION_UNBLOCK, $justification);

    return redirect($buildAdminUrl('/admin/users/' . $user->id . '/edit', $request))
        ->with('message', 'Conta desbloqueada com sucesso.');
});

Route::post('/admin/questions', function (Request $request) use ($hasAdminAccess, $buildAdminUrl) {
    if (!$hasAdminAccess()) {
        return redirect('/')->with('error', 'Acesso restrito ao administrador.');
    }

    $questionId = (int) $request->input('question_id');
    $adminIndexUrl = $buildAdminUrl('/admin', $request);
    $difficulty = (string) $request->input('difficulty', Question::DIFFICULTY_EASY);
    $questionJson = trim((string) $request->input('question_json'));
    $subjectId = (int) $request->input('subject_id');
    $categoryId = (int) $request->input('category_id');
    $prompt = trim((string) $request->input('prompt'));
    $supportText = trim((string) $request->input('support_text'));
    $explanation = trim((string) $request->input('explanation'));
    $alternatives = array_values(array_map(
        fn ($value) => trim((string) $value),
        (array) $request->input('alternatives', [])
    ));

    if ($questionJson !== '') {
        $decodedJson = json_decode($questionJson, true);

        if (!is_array($decodedJson)) {
            return redirect($adminIndexUrl)
                ->with('error', 'O JSON da questao e invalido.')
                ->withInput();
        }

        $normalizedJson = [];
        foreach ($decodedJson as $key => $value) {
            $normalizedKey = Str::upper(trim((string) $key));
            $normalizedKey = str_replace(['Á', 'À', 'Â', 'Ã', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'Ç'], ['A', 'A', 'A', 'A', 'E', 'E', 'I', 'O', 'O', 'O', 'U', 'C'], $normalizedKey);
            $normalizedJson[$normalizedKey] = is_string($value) ? trim($value) : $value;
        }

        $subjectInput = Str::lower(trim((string) ($normalizedJson['MATERIA'] ?? '')));
        $subjectAliases = [
            'ingles' => 'eng_',
            'english' => 'eng_',
            'eng_' => 'eng_',
            'portugues' => 'prt_',
            'prt_' => 'prt_',
            'portuguese' => 'prt_',
            'matematica' => 'mat_',
            'matemática' => 'mat_',
            'mat_' => 'mat_',
            'mathematics' => 'mat_',
        ];
        $subjectSlug = $subjectAliases[$subjectInput] ?? null;

        if (!$subjectSlug) {
            return redirect($adminIndexUrl)
                ->with('error', 'No JSON, informe MATERIA como ingles, portugues ou matematica.')
                ->withInput();
        }

        $subject = Subject::where('slug', $subjectSlug)->where('is_active', true)->first();

        if (!$subject) {
            return redirect($adminIndexUrl)
                ->with('error', 'A materia informada no JSON nao foi encontrada.')
                ->withInput();
        }

        $categoryName = trim((string) ($normalizedJson['CATEGORIA'] ?? ''));

        if ($categoryName === '') {
            return redirect($adminIndexUrl)
                ->with('error', 'No JSON, o campo CATEGORIA e obrigatorio.')
                ->withInput();
        }

        $category = Category::where('subject_id', $subject->id)
            ->where('quiz_type', Category::QUIZ_TYPE_MULTIPLE_CHOICE)
            ->where(function ($query) use ($categoryName) {
                $query->whereRaw('LOWER(name) = ?', [Str::lower($categoryName)])
                    ->orWhereRaw('LOWER(slug) = ?', [Str::lower($categoryName)]);
            })
            ->first();

        if (!$category) {
            return redirect($adminIndexUrl)
                ->with('error', 'A categoria informada no JSON nao foi encontrada para a materia selecionada.')
                ->withInput();
        }

        $subjectId = (int) $subject->id;
        $categoryId = (int) $category->id;
        $prompt = trim((string) ($normalizedJson['PERGUNTA'] ?? ''));
        $supportText = trim((string) ($normalizedJson['TEXTO DE APOIO'] ?? ''));
        $explanation = trim((string) ($normalizedJson['EXPLICACAO'] ?? ''));

        if (isset($normalizedJson['DIFICULDADE'])) {
            $difficulty = Str::lower(trim((string) $normalizedJson['DIFICULDADE']));
        }

        $alternatives = array_values(array_filter([
            trim((string) ($normalizedJson['RESPOSTA'] ?? '')),
            trim((string) ($normalizedJson['RESPOSTA ERRADA 1'] ?? '')),
            trim((string) ($normalizedJson['RESPOSTA ERRADA 2'] ?? '')),
            trim((string) ($normalizedJson['RESPOSTA ERRADA 3'] ?? '')),
            trim((string) ($normalizedJson['RESPOSTA ERRADA 4'] ?? '')),
            trim((string) ($normalizedJson['RESPOSTA ERRADA 5'] ?? '')),
            trim((string) ($normalizedJson['RESPOSTA ERRADA 6'] ?? '')),
            trim((string) ($normalizedJson['RESPOSTA ERRADA 7'] ?? '')),
        ], fn ($value) => $value !== ''));
    }

    $category = Category::where('id', $categoryId)
        ->where('subject_id', $subjectId)
        ->where('quiz_type', Category::QUIZ_TYPE_MULTIPLE_CHOICE)
        ->first();

    if (!$category) {
        return redirect($adminIndexUrl)
            ->with('error', 'Selecione uma materia e uma categoria validas.')
            ->withInput();
    }

    if ($prompt === '') {
        return redirect($adminIndexUrl)
            ->with('error', 'Digite a pergunta.')
            ->withInput();
    }

    if (!array_key_exists($difficulty, Question::difficultyLabels())) {
        return redirect($adminIndexUrl)
            ->with('error', 'Selecione um nivel de dificuldade valido.')
            ->withInput();
    }

    if (count($alternatives) < 8 || collect($alternatives)->filter()->count() < 8) {
        return redirect($adminIndexUrl)
            ->with('error', 'Cadastre 8 alternativas: a primeira correta e mais 7 erradas.')
            ->withInput();
    }

    $normalizedAlternativeSet = collect(array_slice($alternatives, 0, 8))
        ->map(fn ($value) => Str::lower($value))
        ->unique();

    if ($normalizedAlternativeSet->count() < 8) {
        return redirect($adminIndexUrl)
            ->with('error', 'As 8 alternativas precisam ser diferentes entre si.')
            ->withInput();
    }

    $question = $questionId > 0 ? Question::find($questionId) : new Question();

    if (!$question) {
        return redirect($adminIndexUrl)
            ->with('error', 'Questao nao encontrada para edicao.');
    }

    if (!$question->exists) {
        $question->sort_order = (int) Question::where('category_id', $categoryId)->max('sort_order') + 1;
    }

    $question->category_id = $categoryId;
    $question->prompt = $prompt;
    $question->support_text = $supportText !== '' ? $supportText : null;
    $question->explanation = $explanation !== '' ? $explanation : null;
    $question->type = Question::TYPE_MULTIPLE_CHOICE;
    $question->difficulty = $difficulty;
    $question->is_active = true;
    $question->save();

    $question->options()->delete();

    foreach (array_slice($alternatives, 0, 8) as $index => $alternative) {
        $question->options()->create([
            'option_key' => chr(65 + $index),
            'option_text' => $alternative,
            'is_correct' => $index === 0,
            'sort_order' => $index + 1,
        ]);
    }

    return redirect($buildAdminUrl('/admin/questions/' . $question->id . '/edit', $request))
        ->with('message', 'Questao salva com sucesso. A primeira alternativa foi definida como correta.');
});

Route::post('/admin/users', function (Request $request) use ($hasAdminAccess, $getAuthenticatedAccount, $buildAdminUrl) {
    if (!$hasAdminAccess()) {
        return redirect('/')->with('error', 'Acesso restrito ao administrador.');
    }

    $userId = (int) $request->input('user_id');
    $user = $userId > 0 ? User::withTrashed()->find($userId) : new User();
    $adminIndexUrl = $buildAdminUrl('/admin', $request);
    $editUserBaseUrl = $buildAdminUrl('/admin/users/' . $userId . '/edit', $request);

    if (!$user) {
        return redirect($adminIndexUrl)->with('error', 'Usuario nao encontrado.');
    }

    if ($user->exists && $user->trashed()) {
        return redirect($buildAdminUrl('/admin/users/' . $user->id . '/edit', $request))
            ->with('error', 'Contas excluidas logicamente nao podem ser editadas antes da restauracao.');
    }

    $name = trim((string) $request->input('name'));
    $lastName = trim((string) $request->input('last_name'));
    $email = strtolower(trim((string) $request->input('email')));
    $phone = trim((string) $request->input('phone'));
    $bio = trim((string) $request->input('bio'));
    $newPassword = (string) $request->input('new_password');
    $isAdmin = $request->boolean('is_admin');
    $removeAvatar = $request->boolean('remove_avatar');
    $redirectBase = $user->exists ? $buildAdminUrl('/admin/users/' . $user->id . '/edit', $request) : $adminIndexUrl;

    if ($name === '' || $email === '') {
        return redirect($redirectBase)
            ->with('error', 'Nome e e-mail sao obrigatorios.')
            ->withInput();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return redirect($redirectBase)
            ->with('error', 'Digite um e-mail valido.')
            ->withInput();
    }

    $emailExists = User::withTrashed()->where('email', $email)
        ->when($user->exists, fn ($query) => $query->where('id', '!=', $user->id))
        ->exists();

    if ($emailExists) {
        return redirect($redirectBase)
            ->with('error', 'Este e-mail ja esta em uso por outra conta.')
            ->withInput();
    }

    $authenticatedAdmin = $getAuthenticatedAccount();
    if ($authenticatedAdmin && $user->exists && $authenticatedAdmin->id === $user->id && !$isAdmin) {
        return redirect($redirectBase)
            ->with('error', 'Voce nao pode remover o proprio acesso de administrador.')
            ->withInput();
    }

    if (!$user->exists && $newPassword === '') {
        return redirect($adminIndexUrl)
            ->with('error', 'Informe uma senha inicial para criar o usuario.')
            ->withInput();
    }

    if ($newPassword !== '' && strlen($newPassword) < 8) {
        return redirect($redirectBase)
            ->with('error', 'A nova senha precisa ter pelo menos 8 caracteres.')
            ->withInput();
    }

    $user->name = $name;
    $user->last_name = $lastName !== '' ? $lastName : null;
    $user->email = $email;
    $user->phone = $phone !== '' ? $phone : null;
    $user->bio = $bio !== '' ? $bio : null;
    $user->is_admin = $isAdmin;

    if (!$user->exists) {
        $isActive = $request->boolean('is_active', true);
        $user->is_active = $isActive;
        $user->blocked_at = $isActive ? null : now();
    }

    if ($removeAvatar && $user->avatar_path) {
        if (Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = null;
    }

    if ($newPassword !== '') {
        $user->password = $newPassword;
    }

    $user->save();

    if ($authenticatedAdmin && $authenticatedAdmin->id === $user->id) {
        session([
            'is_admin' => $user->isAdmin(),
            'user_name' => $user->displayName(),
            'user_email' => $user->email,
        ]);
    }

    return redirect($buildAdminUrl('/admin/users/' . $user->id . '/edit', $request))
        ->with('message', $userId > 0 ? 'Usuario atualizado com sucesso.' : 'Usuario criado com sucesso.');
});

Route::post('/admin/users/delete', function (Request $request) use ($hasAdminAccess, $getAuthenticatedAccount, $logAdminUserAction, $buildAdminUrl) {
    if (!$hasAdminAccess()) {
        return redirect('/')->with('error', 'Acesso restrito ao administrador.');
    }

    $user = User::withTrashed()->find((int) $request->input('user_id'));
    $adminIndexUrl = $buildAdminUrl('/admin', $request);
    $editUserUrl = $buildAdminUrl('/admin/users/' . (int) $request->input('user_id') . '/edit', $request);

    if (!$user) {
        return redirect($adminIndexUrl)->with('error', 'Usuario nao encontrado.');
    }

    if ($user->trashed()) {
        return redirect($editUserUrl)
            ->with('error', 'Esta conta ja foi excluida logicamente.');
    }

    $authenticatedAdmin = $getAuthenticatedAccount();

    if ($authenticatedAdmin && $authenticatedAdmin->id === $user->id) {
        return redirect($editUserUrl)
            ->with('error', 'Voce nao pode excluir a propria conta.');
    }

    if (!$authenticatedAdmin) {
        return redirect('/login')->with('error', 'Sua sessao expirou. Faca login novamente.');
    }

    $justification = trim((string) $request->input('delete_justification'));

    if ($justification === '') {
        return redirect($editUserUrl)
            ->with('error', 'Informe a justificativa administrativa para excluir a conta.')
            ->withInput();
    }

    $sessionTable = (string) config('session.table', 'sessions');
    DB::table($sessionTable)->where('user_id', $user->id)->delete();

    $user->is_active = false;
    $user->blocked_at = now();
    $user->save();
    $user->delete();

    $logAdminUserAction($authenticatedAdmin, $user, AdminUserAction::ACTION_DELETE, $justification);

    return redirect($adminIndexUrl)->with('message', 'Usuario excluido logicamente com sucesso. O historico foi preservado.');
});

// Lista de lições / categorias
Route::get('/lessons', function (Request $request) use ($hasAuthenticatedUser, $formatSubjectLabel) {
    if (!$hasAuthenticatedUser()) {
        return redirect('/login');
    }

    $selectedSubject = null;
    $selectedSubjectId = (int) $request->query('subject_id', 0);

    if ($selectedSubjectId > 0) {
        $selectedSubject = Subject::where('is_active', true)->find($selectedSubjectId);
    }

    $categories = Category::with('subject')
        ->where('is_active', true)
        ->when($selectedSubject, fn ($query) => $query->where('subject_id', $selectedSubject->id))
        ->orderBy('subject_id')
        ->orderBy('name')
        ->get();

    $subjectLabels = [];
    foreach ($categories as $category) {
        $subjectLabels[$category->id] = $formatSubjectLabel($category->subject);
    }

    return view('categories', [
        'categories' => $categories,
        'subjectLabels' => $subjectLabels,
        'selectedSubject' => $selectedSubject,
        'selectedSubjectLabel' => $selectedSubject ? $formatSubjectLabel($selectedSubject) : null,
    ]);
});

// Quiz por categoria (já existente, mantido igual)
Route::get('/quiz/{category}/reset', function ($category_id) use ($hasAuthenticatedUser, $resetGuestCategoryProgress) {
    if (!$hasAuthenticatedUser()) {
        return redirect('/login');
    }

    if (session('is_guest')) {
        $resetGuestCategoryProgress((int) $category_id);

        return redirect('/quiz/' . $category_id);
    }

    // delete previous attempts for this category so user can start over
    $resetQuery = Score::where('category_id', $category_id);
    if (session('user_id')) {
        $resetQuery->where('user_id', session('user_id'));
    }
    $resetQuery->delete();
    // forget the "already recorded" marker so new run will save again
    session()->forget('quiz_done_' . $category_id);
    return redirect('/quiz/' . $category_id);
});

Route::get('/quiz/{category}', function ($category_id) use ($hasAuthenticatedUser, $getGuestMetrics, $getGuestCategoryRecords, $incrementGuestTrophy, $formatSubjectLabel) {
    if (!$hasAuthenticatedUser()) {
        return redirect('/login');
    }

    $category = Category::with('subject')->find($category_id);

    if (!$category) {
        return redirect('/lessons')->with('error', 'Categoria nao encontrada.');
    }

    $categorySubjectLabel = $formatSubjectLabel($category->subject);
    $categoryDisplayLabel = trim($categorySubjectLabel . ' - ' . $category->name, ' -');

    $userId = session('user_id');
    $isGuest = (bool) session('is_guest');
    $xpPerCorrect = Score::XP_PER_CORRECT_ANSWER;
    if ($isGuest) {
        $totalXp = (int) ($getGuestMetrics()['xp'] ?? 0);
    } else {
        $xpQuery = Score::query();
        if ($userId) {
            $xpQuery->where('user_id', $userId);
        }
        $totalXp = $xpQuery->sum('xp');
    }

    $isVocabularyQuiz = $category->isVocabularyQuiz();

    if ($isGuest) {
        $guestRecords = $getGuestCategoryRecords((int) $category_id);
        $answered = array_values(array_unique(array_filter(array_map(
            fn ($record) => $isVocabularyQuiz ? ($record['word_id'] ?? null) : ($record['question_id'] ?? null),
            $guestRecords
        ))));

        $itemIds = $isVocabularyQuiz
            ? Word::where('category_id', $category_id)->pluck('id')->toArray()
            : Question::where('category_id', $category_id)->where('is_active', true)->pluck('id')->toArray();
    } elseif ($isVocabularyQuiz) {
        $answeredQuery = Score::where('category_id', $category_id)
            ->whereNotNull('word_id');
        if ($userId) {
            $answeredQuery->where('user_id', $userId);
        }
        $answered = $answeredQuery->pluck('word_id')->toArray();
        $itemIds = Word::where('category_id', $category_id)->pluck('id')->toArray();
    } else {
        $answeredQuery = Score::where('category_id', $category_id)
            ->whereNotNull('question_id');
        if ($userId) {
            $answeredQuery->where('user_id', $userId);
        }
        $answered = $answeredQuery->pluck('question_id')->toArray();
        $itemIds = Question::where('category_id', $category_id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();
    }

    $remaining = array_values(array_diff($itemIds, $answered));

    // calculate progress
    $answered_count = count($answered);
    $total_questions = count($itemIds);
    $current_question = $answered_count + 1;

    // if all questions answered, show completion message but record a result and determine trophy
    if (empty($remaining)) {
        if ($isGuest) {
            $guestRecords = $getGuestCategoryRecords((int) $category_id);
            $categoryScore = array_sum(array_map(fn ($record) => (int) ($record['score'] ?? 0), $guestRecords));
        } else {
            $scoreQuery = Score::where('category_id', $category_id);
            if ($userId) {
                $scoreQuery->where('user_id', $userId);
            }
            $categoryScore = $scoreQuery->sum('score');
        }

        // compute trophy based on percentage
        $percentage = $total_questions > 0 ? ($categoryScore / $total_questions) * 100 : 0;
        if ($percentage >= 90) {
            $trophy = 'gold';
        } elseif ($percentage >= 70) {
            $trophy = 'silver';
        } elseif ($percentage >= 50) {
            $trophy = 'bronze';
        } else {
            $trophy = 'none';
        }

        // only save once per session to avoid duplicates on reload
        $sessionKey = 'quiz_done_' . $category_id;
        if (!session($sessionKey)) {
            if ($isGuest) {
                $incrementGuestTrophy($trophy);
            } elseif (\Illuminate\Support\Facades\Schema::hasTable('quiz_results')) {
                \App\Models\QuizResult::create([
                    'category_id'     => $category_id,
                    'user_id'         => $userId,
                    'correct_count'   => $categoryScore,
                    'total_questions' => $total_questions,
                    'trophy'          => $trophy,
                ]);
            }
            session([$sessionKey => true]);
        }

        return view('complete', [
            'category' => $category,
            'categorySubjectLabel' => $categorySubjectLabel,
            'categoryDisplayLabel' => $categoryDisplayLabel,
            'categoryScore' => $categoryScore,
            'total_questions' => $total_questions,
            'trophy' => $trophy,
            'isGuest' => $isGuest,
            'guestMetrics' => $isGuest ? $getGuestMetrics() : null,
        ]);
    }

    if ($isVocabularyQuiz) {
        $wordId = $remaining[array_rand($remaining)];
        $word = Word::find($wordId);

        if (!$word) {
            return redirect('/quiz/' . $category_id)->with('error', 'Palavra nao encontrada.');
        }

        $correctAnswer = $word->portuguese;
        $wrongAnswers = [];

        $wrongFromCategory = Word::where('category_id', $category_id)
            ->where('id', '!=', $word->id)
            ->pluck('portuguese')
            ->unique()
            ->toArray();

        foreach ($wrongFromCategory as $answer) {
            if (count($wrongAnswers) >= 3) {
                break;
            }

            if ($answer !== $correctAnswer && !in_array($answer, $wrongAnswers, true)) {
                $wrongAnswers[] = $answer;
            }
        }

        if (count($wrongAnswers) < 3) {
            $needed = 3 - count($wrongAnswers);
            $wrongFromOthers = Word::where('id', '!=', $word->id)
                ->whereNotIn('portuguese', array_merge($wrongAnswers, [$correctAnswer]))
                ->inRandomOrder()
                ->limit($needed)
                ->pluck('portuguese')
                ->toArray();

            $wrongAnswers = array_merge($wrongAnswers, $wrongFromOthers);
        }

        $answers = collect(array_merge([$correctAnswer], $wrongAnswers))
            ->shuffle()
            ->values()
            ->map(function ($answer, $index) {
                return [
                    'value' => $answer,
                    'label' => $answer,
                    'key' => chr(65 + $index),
                ];
            })
            ->all();

        return view('quiz', [
            'category' => $category,
            'category_id' => $category_id,
            'categorySubjectLabel' => $categorySubjectLabel,
            'categoryDisplayLabel' => $categoryDisplayLabel,
            'quizType' => Category::QUIZ_TYPE_VOCABULARY,
            'questionPrompt' => $word->english,
            'questionSupportText' => $word->example,
            'questionHelperText' => 'Qual e o significado?',
            'questionDifficultyLabel' => null,
            'ttsText' => $word->english,
            'recordField' => 'word_id',
            'recordId' => $word->id,
            'answers' => $answers,
            'current_question' => $current_question,
            'total_questions' => $total_questions,
            'totalXp' => $totalXp,
            'xpPerCorrect' => $xpPerCorrect,
        ]);
    }

    $questionId = $remaining[array_rand($remaining)];
    $question = Question::with('options')->find($questionId);

    if (!$question) {
        return redirect('/quiz/' . $category_id)->with('error', 'Questao nao encontrada.');
    }

    $correctOption = $question->options->firstWhere('is_correct', true);

    if (!$correctOption) {
        return redirect('/quiz/' . $category_id)->with('error', 'Questao sem alternativa correta cadastrada.');
    }

    $answers = $question->options
        ->filter(fn (QuestionOption $option) => !$option->is_correct)
        ->shuffle()
        ->take($question->wrongOptionsToDisplay())
        ->push($correctOption)
        ->shuffle()
        ->values()
        ->map(function ($option, $index) {
            return [
                'value' => (string) $option->id,
                'label' => $option->option_text,
                'key' => $option->option_key ?: chr(65 + $index),
            ];
        })
        ->all();

    return view('quiz', [
        'category' => $category,
        'category_id' => $category_id,
        'categorySubjectLabel' => $categorySubjectLabel,
        'categoryDisplayLabel' => $categoryDisplayLabel,
        'quizType' => Category::QUIZ_TYPE_MULTIPLE_CHOICE,
        'questionPrompt' => $question->prompt,
        'questionSupportText' => $question->support_text,
        'questionHelperText' => 'Selecione a alternativa correta.',
        'questionDifficultyLabel' => Question::difficultyLabels()[$question->difficulty] ?? 'Facil',
        'ttsText' => null,
        'recordField' => 'question_id',
        'recordId' => $question->id,
        'answers' => $answers,
        'current_question' => $current_question,
        'total_questions' => $total_questions,
        'totalXp' => $totalXp,
        'xpPerCorrect' => $xpPerCorrect,
    ]);
});

// Processar resposta do quiz
Route::post('/quiz/{category}/check', function (Request $request, $category_id) use ($hasAuthenticatedUser, $recordGuestAttempt) {
    if (!$hasAuthenticatedUser()) {
        return redirect('/login');
    }

    $category = Category::find($category_id);
    $isGuest = (bool) session('is_guest');

    if (!$category) {
        return redirect('/lessons')->with('error', 'Categoria nao encontrada.');
    }

    if (!$category->isVocabularyQuiz()) {
        $question = Question::with('options')->find($request->question_id);

        if (!$question || $question->category_id !== (int) $category_id) {
            return redirect('/quiz/' . $category_id)->with('error', 'Questao nao encontrada.');
        }

        $selectedOption = $question->options->firstWhere('id', (int) $request->answer);

        if (!$selectedOption) {
            return redirect('/quiz/' . $category_id)->with('error', 'Selecione uma alternativa valida.');
        }

        $correctOption = $question->options->firstWhere('is_correct', true);
        $correct = (bool) $selectedOption->is_correct;
        $earnedXp = Score::xpForAnswer($correct);

        $data = [
            'question_id' => $question->id,
            'selected_option_id' => $selectedOption->id,
            'category_id' => $category_id,
            'score' => $correct ? 1 : 0,
            'xp' => $earnedXp,
            'correct' => $correct,
            'answer' => $selectedOption->option_text,
        ];
        if ($isGuest) {
            $recordGuestAttempt((int) $category_id, $data);
        } elseif (session('user_id')) {
            $data['user_id'] = session('user_id');
            Score::create($data);
        } else {
            Score::create($data);
        }

        if ($correct) {
            return redirect('/quiz/' . $category_id)
                ->with('result', 'Correto! +' . $earnedXp . ' XP')
                ->with('correct', true);
        }

        return redirect('/quiz/' . $category_id)
            ->with('result', 'Incorreto. A resposta certa e: ' . ($correctOption?->option_text ?? 'indisponivel'))
            ->with('correct', false);
    }

    $word = Word::find($request->word_id);

    if (!$word) {
        return redirect('/quiz/' . $category_id)->with('error', 'Palavra nao encontrada.');
    }

    $correct = $request->answer === $word->portuguese;
    $earnedXp = Score::xpForAnswer($correct);

    // Salvar score (associar usuário se houver)
    $data = [
        'word_id' => $word->id,
        'category_id' => $category_id,
        'score' => $correct ? 1 : 0,
        'xp' => $earnedXp,
        'correct' => $correct,
        'answer' => $request->answer,
    ];
    if ($isGuest) {
        $recordGuestAttempt((int) $category_id, $data);
    } elseif (session('user_id')) {
        $data['user_id'] = session('user_id');
        Score::create($data);
    } else {
        Score::create($data);
    }

    if ($correct) {
        return redirect('/quiz/' . $category_id)
            ->with('result', 'Correto! +' . $earnedXp . ' XP')
            ->with('correct', true);
    } else {
        return redirect('/quiz/' . $category_id)
            ->with('result', 'Incorreto. A resposta certa e: ' . $word->portuguese)
            ->with('correct', false);
    }
});

// Nova rota para Vocabulary
Route::get('/vocabulary', function (Request $request) use ($hasAuthenticatedUser, $formatSubjectLabel, $buildPublicSubjectLegend) {
    if (!$hasAuthenticatedUser()) {
        return redirect('/login');
    }

    $selectedSubject = null;
    $selectedSubjectId = (int) $request->query('subject_id', 0);

    if ($selectedSubjectId > 0) {
        $selectedSubject = Subject::where('is_active', true)->find($selectedSubjectId);
    }

    $words = \App\Models\Word::with('category.subject')
        ->when($selectedSubject, fn ($query) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('subject_id', $selectedSubject->id)))
        ->orderBy('category_id')
        ->orderBy('english')
        ->get();

    $groupedWords = $words
        ->groupBy('category_id')
        ->map(function ($categoryWords) use ($formatSubjectLabel) {
            $firstWord = $categoryWords->first();
            $category = $firstWord?->category;
            $subjectLabel = $formatSubjectLabel($category?->subject);

            return [
                'category' => $category,
                'subjectLabel' => $subjectLabel,
                'categoryDisplayLabel' => trim($subjectLabel . ' - ' . ($category->name ?? 'Sem categoria'), ' -'),
                'words' => $categoryWords,
            ];
        })
        ->values();

    return view('vocabulary', [
        'groupedWords' => $groupedWords,
        'publicSubjectLegend' => $buildPublicSubjectLegend(),
        'selectedSubject' => $selectedSubject,
        'selectedSubjectLabel' => $selectedSubject ? $formatSubjectLabel($selectedSubject) : null,
    ]);
})->name('vocabulary');

// Página de pontuação agregada
Route::get('/score', function () use ($buildLeaderboard, $hasAuthenticatedUser, $buildPublicSubjectLegend) {
    if (!$hasAuthenticatedUser()) {
        return redirect('/login');
    }

    $isGuest = (bool) session('is_guest');
    $publicSubjectLegend = $buildPublicSubjectLegend();

    if ($isGuest) {
        $guestMetrics = session('guest_metrics', []);

        return view('score', [
            'score' => (int) ($guestMetrics['score'] ?? 0),
            'xp' => (int) ($guestMetrics['xp'] ?? 0),
            'goldCount' => (int) ($guestMetrics['trophies']['gold'] ?? 0),
            'silverCount' => (int) ($guestMetrics['trophies']['silver'] ?? 0),
            'bronzeCount' => (int) ($guestMetrics['trophies']['bronze'] ?? 0),
            'leaderboard' => collect(),
            'currentUserRank' => null,
            'isGuest' => true,
            'publicSubjectLegend' => $publicSubjectLegend,
        ]);
    }

    $query = \App\Models\Score::query();
    if (session('user_id')) {
        $query->where('user_id', session('user_id'));
    }
    $score = $query->sum('score');
    $xp = $query->sum('xp');

    $goldCount = \App\Models\QuizResult::where('trophy','gold');
    $silverCount = \App\Models\QuizResult::where('trophy','silver');
    $bronzeCount = \App\Models\QuizResult::where('trophy','bronze');
    if (session('user_id')) {
        $goldCount->where('user_id', session('user_id'));
        $silverCount->where('user_id', session('user_id'));
        $bronzeCount->where('user_id', session('user_id'));
    }

    $leaderboard = $buildLeaderboard();
    $currentUserRank = $leaderboard->firstWhere('id', session('user_id'));

    return view('score', [
        'score' => $score,
        'xp' => $xp,
        'goldCount' => $goldCount->count(),
        'silverCount' => $silverCount->count(),
        'bronzeCount' => $bronzeCount->count(),
        'leaderboard' => $leaderboard->take(5),
        'currentUserRank' => $currentUserRank,
        'isGuest' => false,
        'publicSubjectLegend' => $publicSubjectLegend,
    ]);
});

Route::get('/ranking', function () use ($buildLeaderboard, $hasAuthenticatedUser, $buildPublicSubjectLegend) {
    if (!$hasAuthenticatedUser()) {
        return redirect('/login');
    }

    $leaderboard = $buildLeaderboard();

    return view('ranking', [
        'leaderboard' => $leaderboard,
        'currentUserId' => session('user_id'),
        'isGuest' => (bool) session('is_guest'),
        'publicSubjectLegend' => $buildPublicSubjectLegend(),
    ]);
});


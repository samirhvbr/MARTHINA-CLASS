@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-11">
        @if($flashMessage)
            <div class="alert alert-success">{{ $flashMessage }}</div>
        @endif

        @if($flashError)
            <div class="alert alert-danger">{{ $flashError }}</div>
        @endif

        <div class="card p-4 p-md-5 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="mb-1">Painel Admin</h1>
                    <p class="text-muted mb-0">Cadastre e edite perguntas de multipla escolha por materia e categoria.</p>
                </div>
                <a href="/" class="btn btn-outline-secondary">Voltar ao inicio</a>
            </div>

            <div class="alert alert-warning small">
                A primeira alternativa e sempre a correta. Cadastre 8 alternativas por pergunta: 1 correta e 7 erradas. Facil mostra 4 alternativas, normal mostra 5 e dificil mostra 6.
            </div>

            <form method="POST" action="/admin/questions">
                @csrf
                <input type="hidden" name="question_id" value="{{ $editingQuestionId }}">
                <input type="hidden" name="user_search" value="{{ $userSearch }}">
                <input type="hidden" name="user_status" value="{{ $userStatus }}">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="subject_id" class="form-label">Materia</label>
                        <select name="subject_id" id="subject_id" class="form-select" required>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected($selectedSubjectId === $subject->id)>{{ $subjectAdminLabels[$subject->id] ?? $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select name="category_id" id="category_id" class="form-select" data-selected-category-id="{{ $selectedCategoryId }}" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-subject-id="{{ $category->subject_id }}" @selected($selectedCategoryId === $category->id)>
                                    {{ $categoryAdminLabels[$category->id] ?? $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="prompt" class="form-label">Pergunta</label>
                    <textarea name="prompt" id="prompt" rows="3" class="form-control" required>{{ $editingQuestionPrompt }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="question_json" class="form-label">Importar questao por JSON</label>
                    <textarea name="question_json" id="question_json" rows="12" class="form-control" placeholder="Cole aqui o JSON da questao. Se preencher este campo, ele tera prioridade sobre os campos manuais.">{{ $questionJsonPayload }}</textarea>
                    <div class="form-text">Use este formato para importar uma questao completa. O campo MATERIA aceita ingles, portugues ou matematica.</div>
                    <pre class="bg-light border rounded p-3 mt-3 small mb-0"><code>{{ $questionJsonTemplate }}</code></pre>
                </div>

                <div class="mb-3">
                    <label for="support_text" class="form-label">Texto de apoio</label>
                    <textarea name="support_text" id="support_text" rows="2" class="form-control">{{ $editingQuestionSupportText }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="explanation" class="form-label">Explicacao</label>
                    <textarea name="explanation" id="explanation" rows="2" class="form-control">{{ $editingQuestionExplanation }}</textarea>
                </div>

                <div class="mb-4">
                    <label for="difficulty" class="form-label">Dificuldade</label>
                    <select name="difficulty" id="difficulty" class="form-select" required>
                        @foreach($difficultyLabels as $difficultyValue => $difficultyLabel)
                            @php
                                $difficultyDescription = '(6 alternativas)';

                                if ($difficultyValue === 'easy') {
                                    $difficultyDescription = '(4 alternativas)';
                                } elseif ($difficultyValue === 'normal') {
                                    $difficultyDescription = '(5 alternativas)';
                                }
                            @endphp
                            <option value="{{ $difficultyValue }}" @selected($editingQuestionDifficulty === $difficultyValue)>
                                {{ $difficultyLabel }}
                                {{ $difficultyDescription }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    @foreach($alternatives as $index => $alternative)
                        @php
                            $alternativeLabel = 'Alternativa errada ' . $index;

                            if ($index === 0) {
                                $alternativeLabel = 'Alternativa correta';
                            }
                        @endphp
                        <div class="col-md-6 mb-3">
                            <label for="alternative_{{ $index }}" class="form-label">{{ $alternativeLabel }}</label>
                            <input
                                type="text"
                                id="alternative_{{ $index }}"
                                name="alternatives[]"
                                class="form-control"
                                value="{{ $alternative }}"
                                required
                            >
                        </div>
                    @endforeach
                </div>

                <div class="d-flex gap-2 flex-wrap mt-3">
                    <button type="submit" class="btn btn-custom">{{ $questionSubmitLabel }}</button>
                    <a href="/admin?subject_id={{ $selectedSubjectId }}&category_id={{ $selectedCategoryId }}&user_search={{ urlencode($userSearch) }}&user_status={{ urlencode($userStatus) }}" class="btn btn-outline-secondary">Nova questao</a>
                </div>
            </form>
        </div>

        <div class="card p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h2 class="h4 mb-1">Questoes cadastradas</h2>
                    <p class="text-muted mb-0">Selecione uma pergunta existente para editar.</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Categoria</th>
                            <th>Pergunta</th>
                            <th>Dificuldade</th>
                            <th>Alternativas</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $question)
                            @php
                                $questionSubjectId = 0;
                                $questionSubjectName = '';
                                $questionCategoryName = '';
                                $questionDifficultyLabel = 'Facil';

                                if ($question->category) {
                                    $questionSubjectId = $question->category->subject_id;
                                    $questionCategoryName = $categoryAdminLabels[$question->category->id] ?? $question->category->name;
                                    $questionSubjectName = $subjectAdminLabels[$questionSubjectId] ?? '';
                                }

                                if (array_key_exists($question->difficulty, $difficultyLabels)) {
                                    $questionDifficultyLabel = $difficultyLabels[$question->difficulty];
                                }
                            @endphp
                            <tr>
                                <td>{{ $questionSubjectName }}</td>
                                <td>{{ $questionCategoryName }}</td>
                                <td>{{ $question->prompt }}</td>
                                <td>{{ $questionDifficultyLabel }}</td>
                                <td>{{ $question->options->count() }}</td>
                                <td class="text-end">
                                    <a href="/admin/questions/{{ $question->id }}/edit?subject_id={{ $questionSubjectId }}&category_id={{ $question->category_id }}&user_search={{ urlencode($userSearch) }}&user_status={{ urlencode($userStatus) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhuma questao encontrada para o filtro atual.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4 p-md-5 mt-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h2 class="h4 mb-1">Gestao de usuarios</h2>
                    <p class="text-muted mb-0">Busque usuarios, crie contas manualmente, bloqueie acessos e revise o historico de atividade.</p>
                </div>
            </div>

            <form method="GET" action="/admin" class="row g-3 mb-4">
                <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">

                <div class="col-md-6">
                    <label for="user_search" class="form-label">Buscar usuario</label>
                    <input type="text" id="user_search" name="user_search" class="form-control" value="{{ $userSearch }}" placeholder="Nome, sobrenome, e-mail ou telefone">
                </div>

                <div class="col-md-3">
                    <label for="user_status" class="form-label">Status</label>
                    <select id="user_status" name="user_status" class="form-select">
                        <option value="all" @selected($userStatus === 'all')>Todos</option>
                        <option value="active" @selected($userStatus === 'active')>Ativos</option>
                        <option value="blocked" @selected($userStatus === 'blocked')>Bloqueados</option>
                        <option value="deleted" @selected($userStatus === 'deleted')>Excluidos</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Buscar</button>
                    <a href="/admin?subject_id={{ $selectedSubjectId }}&category_id={{ $selectedCategoryId }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="border rounded p-4 h-100">
                        <h3 class="h5 mb-3">{{ $editingUserFormTitle }}</h3>

                        @if($editingUser)
                            <div class="border rounded p-3 mb-4 bg-light-subtle">
                                <div class="d-flex align-items-center gap-3">
                                    @if($editingUserAvatarPath)
                                        <img src="/profile/avatar/{{ $editingUserId }}" alt="Foto de {{ $editingUserDisplayName }}" class="rounded-circle" style="width: 72px; height: 72px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; font-size: 1.5rem; font-weight: 700; color: #555;">
                                            {{ strtoupper(substr($editingUserName, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="fw-semibold">{{ $editingUserDisplayName }}</div>
                                        <div class="text-muted small">{{ $editingUserEmail }}</div>
                                        <div class="small mt-1">
                                            <span class="badge {{ $editingUserRoleBadgeClass }}">{{ $editingUserRoleLabel }}</span>
                                            <span class="badge {{ $editingUserStatusBadgeClass }}">{{ $editingUserStatusLabel }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3 g-2 small">
                                    <div class="col-6">
                                        <div class="text-muted">Respostas</div>
                                        <div class="fw-semibold">{{ $editingUser->scores_count }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Quizzes</div>
                                        <div class="fw-semibold">{{ $editingUser->quiz_results_count }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Telefone</div>
                                        @if($editingUser->phone)
                                            <div class="fw-semibold">{{ $editingUser->phone }}</div>
                                        @else
                                            <div class="fw-semibold">Nao informado</div>
                                        @endif
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Bloqueado em</div>
                                        @if($editingUser->blocked_at)
                                            <div class="fw-semibold">{{ $editingUser->blocked_at->format('d/m/Y H:i') }}</div>
                                        @else
                                            <div class="fw-semibold">Nunca</div>
                                        @endif
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted">Excluido em</div>
                                        @if($editingUser->deleted_at)
                                            <div class="fw-semibold">{{ $editingUser->deleted_at->format('d/m/Y H:i') }}</div>
                                        @else
                                            <div class="fw-semibold">Nao</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="/admin/users">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $editingUserId }}">
                            <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                            <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                            <input type="hidden" name="user_search" value="{{ $userSearch }}">
                            <input type="hidden" name="user_status" value="{{ $userStatus }}">

                            <fieldset {{ $editingUserFieldsetDisabled }}>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="admin_user_name" class="form-label">Nome</label>
                                    <input type="text" id="admin_user_name" name="name" class="form-control" value="{{ $editingUserName }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="admin_user_last_name" class="form-label">Sobrenome</label>
                                    <input type="text" id="admin_user_last_name" name="last_name" class="form-control" value="{{ $editingUserLastName }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="admin_user_email" class="form-label">E-mail</label>
                                <input type="email" id="admin_user_email" name="email" class="form-control" value="{{ $editingUserEmail }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="admin_user_phone" class="form-label">Telefone</label>
                                <input type="text" id="admin_user_phone" name="phone" class="form-control" value="{{ $editingUserPhone }}">
                            </div>

                            <div class="mb-3">
                                <label for="admin_user_bio" class="form-label">Bio</label>
                                <textarea id="admin_user_bio" name="bio" rows="3" class="form-control">{{ $editingUserBio }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="admin_user_new_password" class="form-label">{{ $editingUserPasswordLabel }}</label>
                                <input type="password" id="admin_user_new_password" name="new_password" class="form-control" minlength="8" @if(!$isEditingUser) required @endif>
                                <div class="form-text">{{ $editingUserPasswordHelp }}</div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="admin_user_is_admin" name="is_admin" value="1" @checked($editingUserIsAdmin)>
                                <label class="form-check-label" for="admin_user_is_admin">Usuario administrador</label>
                            </div>

                            @if(!$editingUser)
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="admin_user_is_active" name="is_active" value="1" @checked($editingUserIsActive)>
                                    <label class="form-check-label" for="admin_user_is_active">Usuario ativo</label>
                                </div>
                            @else
                                <div class="mb-4">
                                    <label class="form-label">Status atual</label>
                                    <div>
                                        @if($editingUser->trashed())
                                            <span class="badge bg-dark">Excluido</span>
                                        @elseif($editingUser->is_active)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-danger">Bloqueado</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($editingUserIsTrashed)
                                <div class="alert alert-secondary mb-4">
                                    Esta conta esta excluida logicamente. Os dados e historicos foram preservados e podem ser restaurados abaixo.
                                </div>
                            @elseif($editingUser && !$editingUser->is_active)
                                <div class="alert alert-warning mb-4">
                                    Esta conta esta bloqueada manualmente. Use o desbloqueio administrativo abaixo para liberar novo acesso.
                                </div>
                            @endif

                            @if($editingUserAvatarPath)
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="admin_user_remove_avatar" name="remove_avatar" value="1">
                                    <label class="form-check-label" for="admin_user_remove_avatar">Remover foto de perfil</label>
                                </div>
                            @endif

                            <div class="d-flex gap-2 flex-wrap">
                                @unless($editingUserIsTrashed)
                                    <button type="submit" class="btn btn-custom">{{ $editingUserSubmitLabel }}</button>
                                @endunless
                                @if($editingUser)
                                    <a href="/admin?subject_id={{ $selectedSubjectId }}&category_id={{ $selectedCategoryId }}&user_search={{ urlencode($userSearch) }}&user_status={{ urlencode($userStatus) }}" class="btn btn-outline-secondary">Novo usuario</a>
                                @endif
                            </div>

                            </fieldset>
                        </form>

                        @if($editingUser)
                            <hr class="my-4">

                            <div class="d-flex gap-2 flex-wrap">
                                @if($editingUserIsTrashed)
                                    <form method="POST" action="/admin/users/restore" class="w-100">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $editingUserId }}">
                                        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                                        <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                                        <input type="hidden" name="user_search" value="{{ $userSearch }}">
                                        <input type="hidden" name="user_status" value="{{ $userStatus }}">
                                        <div class="mb-3">
                                            <label for="restore_justification" class="form-label">Justificativa da restauracao</label>
                                            <textarea id="restore_justification" name="restore_justification" rows="3" class="form-control" placeholder="Explique por que a conta esta sendo restaurada." required>{{ $restoreJustification }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-outline-success">Restaurar conta</button>
                                    </form>
                                @else
                                    <div class="w-100 d-grid gap-3">
                                        @if($editingUser->is_active)
                                            <form method="POST" action="/admin/users/block" class="w-100">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $editingUserId }}">
                                                <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                                                <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                                                <input type="hidden" name="user_search" value="{{ $userSearch }}">
                                                <input type="hidden" name="user_status" value="{{ $userStatus }}">
                                                <div class="mb-3">
                                                    <label for="block_justification" class="form-label">Justificativa do bloqueio</label>
                                                    <textarea id="block_justification" name="block_justification" rows="3" class="form-control" placeholder="Explique o motivo administrativo do bloqueio manual." required>{{ $blockJustification }}</textarea>
                                                </div>
                                                <button type="submit" class="btn btn-outline-warning">Bloquear conta</button>
                                            </form>
                                        @else
                                            <form method="POST" action="/admin/users/unblock" class="w-100">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $editingUserId }}">
                                                <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                                                <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                                                <input type="hidden" name="user_search" value="{{ $userSearch }}">
                                                <input type="hidden" name="user_status" value="{{ $userStatus }}">
                                                <div class="mb-3">
                                                    <label for="unblock_justification" class="form-label">Justificativa do desbloqueio</label>
                                                    <textarea id="unblock_justification" name="unblock_justification" rows="3" class="form-control" placeholder="Explique por que a conta esta sendo desbloqueada." required>{{ $unblockJustification }}</textarea>
                                                </div>
                                                <button type="submit" class="btn btn-outline-success">Desbloquear conta</button>
                                            </form>
                                        @endif

                                        <form method="POST" action="/admin/users/delete" class="w-100" onsubmit="return confirm('Confirma a exclusao logica deste usuario? O acesso sera removido, mas o historico sera preservado para restauracao futura.');">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $editingUserId }}">
                                            <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                                            <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                                            <input type="hidden" name="user_search" value="{{ $userSearch }}">
                                            <input type="hidden" name="user_status" value="{{ $userStatus }}">
                                            <div class="mb-3">
                                                <label for="delete_justification" class="form-label">Justificativa da exclusao</label>
                                                <textarea id="delete_justification" name="delete_justification" rows="3" class="form-control" placeholder="Explique o motivo administrativo da exclusao logica." required>{{ $deleteJustification }}</textarea>
                                            </div>
                                            <button type="submit" class="btn btn-outline-danger">Excluir logicamente</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="table-responsive border rounded">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Contato</th>
                                    <th>Status</th>
                                    <th>Atividade</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php
                                        $listUserPhone = 'Sem telefone';
                                        $listUserRowClass = '';

                                        if ($user->phone) {
                                            $listUserPhone = $user->phone;
                                        }

                                        if ($editingUser && $editingUser->id === $user->id) {
                                            $listUserRowClass = 'table-warning';
                                        }
                                    @endphp
                                    <tr class="{{ $listUserRowClass }}">
                                        <td>
                                            <div class="fw-semibold">{{ $user->displayName() }}</div>
                                            <small class="text-muted">ID {{ $user->id }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $user->email }}</div>
                                            <small class="text-muted">{{ $listUserPhone }}</small>
                                        </td>
                                        <td>
                                            @if($user->is_admin)
                                                <span class="badge bg-warning text-dark">Admin</span>
                                            @else
                                                <span class="badge bg-secondary">Aluno</span>
                                            @endif
                                            @if($user->trashed())
                                                <span class="badge bg-dark">Excluido</span>
                                            @elseif($user->is_active)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Bloqueado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $user->scores_count }} respostas</div>
                                            <small class="text-muted">{{ $user->quiz_results_count }} quizzes concluidos</small>
                                        </td>
                                        <td class="text-end">
                                            <a href="/admin/users/{{ $user->id }}/edit?subject_id={{ $selectedSubjectId }}&category_id={{ $selectedCategoryId }}&user_search={{ urlencode($userSearch) }}&user_status={{ urlencode($userStatus) }}" class="btn btn-sm btn-outline-primary">Gerenciar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Nenhum usuario encontrado para a busca atual.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($editingUser)
                <hr class="my-4">

                <div class="row g-4">
                    <div class="col-lg-6">
                        <h3 class="h5 mb-3">Historico de respostas</h3>
                        <div class="table-responsive border rounded">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Categoria</th>
                                        <th>Conteudo</th>
                                        <th>Resultado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($editingUserScores as $score)
                                        @php
                                            $scoreDate = '-';
                                            $scoreCategoryName = 'Sem categoria';
                                            $scoreContent = 'Registro sem conteudo';
                                            $scoreBadgeClass = 'bg-danger';
                                            $scoreResultLabel = 'Erro';

                                            if ($score->created_at) {
                                                $scoreDate = $score->created_at->format('d/m/Y H:i');
                                            }

                                            if ($score->category) {
                                                $scoreCategoryName = $score->category->name;
                                            }

                                            if ($score->correct) {
                                                $scoreBadgeClass = 'bg-success';
                                                $scoreResultLabel = 'Correto';
                                            }

                                            if ($score->question) {
                                                $scoreContent = $score->question->prompt;
                                            } elseif ($score->word) {
                                                $scoreContent = $score->word->english;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $scoreDate }}</td>
                                            <td>{{ $scoreCategoryName }}</td>
                                            <td>{{ $scoreContent }}</td>
                                            <td>
                                                <span class="badge {{ $scoreBadgeClass }}">{{ $scoreResultLabel }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Sem respostas registradas para este usuario.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <h3 class="h5 mb-3">Historico de quizzes</h3>
                        <div class="table-responsive border rounded">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Categoria</th>
                                        <th>Acertos</th>
                                        <th>Trofeu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($editingUserQuizResults as $result)
                                        @php
                                            $resultDate = '-';
                                            $resultCategoryName = 'Sem categoria';

                                            if ($result->created_at) {
                                                $resultDate = $result->created_at->format('d/m/Y H:i');
                                            }

                                            if ($result->category) {
                                                $resultCategoryName = $result->category->name;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $resultDate }}</td>
                                            <td>{{ $resultCategoryName }}</td>
                                            <td>{{ $result->correct_count }}/{{ $result->total_questions }}</td>
                                            <td>{{ ucfirst($result->trophy) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Sem quizzes concluidos para este usuario.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="h5 mb-3">Historico administrativo</h3>
                    <div class="table-responsive border rounded">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Acao</th>
                                    <th>Administrador</th>
                                    <th>Justificativa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($editingUserAdminActions as $action)
                                    <tr>
                                        <td>
                                            @if($action->created_at)
                                                {{ $action->created_at->format('d/m/Y H:i') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $action->actionLabel() }}</td>
                                        <td>
                                            @if($action->adminUser)
                                                {{ $action->adminUser->displayName() }}
                                            @else
                                                Administrador removido
                                            @endif
                                        </td>
                                        <td>{{ $action->justification }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Nenhuma acao administrativa registrada para este usuario.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const subjectSelect = document.getElementById('subject_id');
const categorySelect = document.getElementById('category_id');
const categoryOptions = @json($categoryOptionsData);

function filterCategories() {
    const subjectId = Number(subjectSelect.value);
    const selectedCategoryId = Number(categorySelect.dataset.selectedCategoryId || categorySelect.value || 0);
    const filteredCategories = categoryOptions.filter((category) => Number(category.subject_id) === subjectId);

    categorySelect.innerHTML = '';

    filteredCategories.forEach((category, index) => {
        const option = document.createElement('option');
        option.value = String(category.id);
        option.textContent = category.label;

        const shouldSelect = selectedCategoryId > 0
            ? Number(category.id) === selectedCategoryId
            : index === 0;

        if (shouldSelect) {
            option.selected = true;
        }

        categorySelect.appendChild(option);
    });

    if (!categorySelect.value && filteredCategories.length > 0) {
        categorySelect.value = String(filteredCategories[0].id);
    }

    categorySelect.dataset.selectedCategoryId = categorySelect.value || '';
}

subjectSelect.addEventListener('change', () => {
    categorySelect.dataset.selectedCategoryId = '';
    filterCategories();
});

categorySelect.addEventListener('change', () => {
    categorySelect.dataset.selectedCategoryId = categorySelect.value || '';
});

filterCategories();
</script>
@endsection

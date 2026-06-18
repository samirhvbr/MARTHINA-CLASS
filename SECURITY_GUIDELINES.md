# Marthina Learning — Diretrizes de Segurança

**Versão:** 1.0
**Data:** 2026-06-18
**Responsável pelos dados:** Samir Hanna Verza
**Escopo:** Plataforma educacional infantil em Laravel com **contas de usuário**,
**modo convidado** e **painel administrativo**.

> Este documento é a **fonte única** de princípios de segurança do projeto.
> Segurança é critério de aceite, não item opcional.

---

## Sumário

1. [Princípio Fundamental](#1-princípio-fundamental)
2. [Modelo de Ameaças](#2-modelo-de-ameaças)
3. [Regras Gerais (obrigatórias)](#3-regras-gerais-obrigatórias)
4. [Vulnerabilidades e Como Evitá-las](#4-vulnerabilidades-e-como-evitá-las)
5. [Autenticação e Sessão](#5-autenticação-e-sessão)
6. [Painel Administrativo](#6-painel-administrativo)
7. [Segredos e Configuração](#7-segredos-e-configuração)
8. [Rate Limiting (lacuna prioritária)](#8-rate-limiting-lacuna-prioritária)
9. [Upload de Arquivos (avatar)](#9-upload-de-arquivos-avatar)
10. [Tratamento de Erros](#10-tratamento-de-erros)
11. [LGPD — Dados de uma criança](#11-lgpd--dados-de-uma-criança)
12. [Manutenção de Dependências](#12-manutenção-de-dependências)
13. [Resposta a Incidentes](#13-resposta-a-incidentes)
14. [Checklist Rápido (pré-commit)](#14-checklist-rápido-pré-commit)

---

## 1. Princípio Fundamental

> **Segurança > Produtividade > Performance > Facilidade**

**Princípios derivados:**

- **Menor privilégio.** Aluno comum nunca toca dados de outros; admin é exceção controlada.
- **Fail-closed.** Sem permissão clara, **nega**. O gate de admin checa `is_admin` a cada requisição.
- **Defesa em profundidade.** Validação no front (UX) + validação no servidor + constraint no banco.
- **Não confiar em entrada.** Todo input (body, query, headers, cookies de sessão de convidado) é hostil.

---

## 2. Modelo de Ameaças

A aplicação tem **três superfícies**: páginas públicas de auth (`/login`,
`/register`, `/forgot-password`, `/reset-password`, `/guest-login`), a área do
usuário autenticado (`/`, `/quiz/*`, `/profile`, `/ranking`, ...) e o painel
admin (`/admin/*`).

| Ator | Vetor típico | Mitigação principal |
|---|---|---|
| **Bot / script** | Cadastro em massa, brute-force de login, spam de recuperação | Honeypot + time-trap + pergunta humana; **rate limit (a implementar)** |
| **Atacante externo** | Roubo de conta, escalonamento para admin | Hash de senha, sessão regenerada, `is_admin` fora do `$fillable` de input |
| **Usuário malicioso logado** | XSS via nome/bio exibidos; acessar dados de outro | Escaping `{{ }}`; checagem de dono nas ações |
| **Scanner de vulnerabilidades** | Acesso a `.env`, `.git`, `storage/logs` | Bloqueio no vhost Nginx, `root` no `public/` |
| **Erro humano em deploy** | `APP_DEBUG=true`, `ADMIN_PASSWORD` fraca commitada | `.gitignore`, checklist, senha forte antes de migrar |

> ⚠️ O painel admin **exibe dados vindos do usuário** (nome, sobrenome, e-mail,
> telefone, bio). Esses valores são entrada hostil e **devem** ser exibidos
> sempre com escaping `{{ }}` — nunca `{!! !!}`.

---

## 3. Regras Gerais (obrigatórias)

- Princípio de menor privilégio em todo o código.
- Nunca confie em nenhuma entrada do usuário/cliente.
- Valide, sanitize e escape **em todas as camadas**.
- Use Eloquent / Query Builder parametrizado em **100%** das queries.
- Nunca concatene strings em SQL, comandos shell, HTML ou headers.
- Todo output para o navegador é escapado com `{{ }}`.
- **Fail-closed** em qualquer decisão de autorização (gate de admin, dono do recurso).
- **Nunca** logar dados sensíveis (senha, hash, tokens, PII desnecessária).
- **Nunca** commitar `.env`, senha real, chaves ou credenciais.
- **Banco: sempre MariaDB/MySQL ou PostgreSQL — nunca SQLite.**

---

## 4. Vulnerabilidades e Como Evitá-las

### 4.1 SQL Injection

- **Sempre** Eloquent ou Query Builder parametrizado.
- Buscas do admin (`name like %...%`, filtros) usam binding — nunca concatenação.
- Onde houver `whereRaw()` (ex.: comparação case-insensitive de categoria), usar
  **bind explícito** `->whereRaw('LOWER(name) = ?', [$valor])`. Nunca interpolar variável.

### 4.2 XSS (Cross-Site Scripting)

- **Nunca** `{!! $var !!}` com dado de usuário (nome, sobrenome, bio, e-mail).
- Escaping automático do Blade (`{{ $var }}`) é o padrão.
- Dados do servidor para o JS: `@json($var)` — nunca interpolar variável crua em `<script>`.
- CSP restritivo nos headers do Nginx em produção.

### 4.3 CSRF (Cross-Site Request Forgery)

- `@csrf` em **todos** os formulários (login, registro, perfil, admin, quiz).
- Middleware web de verificação de CSRF sempre ativo.

### 4.4 Mass Assignment / escalonamento de privilégio

- `$fillable` **explícito** em todos os models.
- **`is_admin` nunca deve vir de input não confiável.** No painel, só um admin
  autenticado altera a flag, e há guarda para o admin não remover o próprio acesso.

### 4.5 Validação de Input

- Validar tipos, tamanho (`max:`) e formato (e-mail via `filter_var`) no servidor.
- Senha mínima de 8 caracteres em registro/redefinição/troca.
- **Direção-alvo:** migrar a validação inline das closures para **Form Requests**
  (`app/Http/Requests/`), centralizando regras e mensagens.

### 4.6 Quebra de controle de acesso

- Rotas autenticadas checam `hasAuthenticatedUser()`; rotas admin checam `hasAdminAccess()`.
- Ações sobre outro usuário verificam existência e estado (`trashed()`, `is_active`).
- Acesso ao avatar (`/profile/avatar/{user}`) só serve arquivo existente; caso contrário `404`.

---

## 5. Autenticação e Sessão

- **Senhas**: cast `hashed` no model `User` (bcrypt, `BCRYPT_ROUNDS=12`).
  Comparação sempre via `Hash::check` / `Auth::attempt`.
- **Anti-bot no registro**: honeypot (`company`), time-trap (mín. 3s) e pergunta
  humana (soma de dois números guardada em sessão). Honeypot também em login e recuperação.
- **Rotação de sessão**: `session()->regenerate()` no login (e ao redefinir senha);
  `invalidate()` + `regenerateToken()` no logout.
- **Invalidação ativa**: ao **bloquear**, **excluir** ou **redefinir senha** de um
  usuário, as linhas de sessão dele são removidas (`sessions.user_id`).
- **Recuperação de senha**: token aleatório (`Str::random(64)`) gravado **hasheado**,
  com expiração (`auth.passwords.users.expire`) e **uso único** (apagado após uso).
- **Mensagens genéricas**: login não distingue "e-mail inexistente" de "senha errada";
  recuperação sempre responde de forma neutra ("se o e-mail estiver cadastrado...").
- **Convidado**: sessão efêmera (`expire_on_close`), token de navegador em cookie;
  progresso só na sessão, nunca persistido no banco.

---

## 6. Painel Administrativo

- Acesso por flag `is_admin` no `User`, checada a cada requisição (`hasAdminAccess()`).
- **Toda ação destrutiva exige justificativa** e é registrada em `AdminUserAction`
  (restaurar, bloquear, desbloquear, excluir).
- **Exclusão é lógica** (soft delete) — o histórico (`scores`, `quiz_results`,
  ações) é preservado; a conta pode ser restaurada.
- **Auto-proteção do admin**: não pode bloquear, excluir nem remover o próprio
  acesso de administrador.
- Importação de questões por JSON valida matéria/categoria existentes e **8
  alternativas distintas** (1 correta + 7 erradas) antes de gravar.

---

## 7. Segredos e Configuração

- **`.env` nunca é commitado.** Está no `.gitignore`.
- **`.env.example` é a fonte da verdade das chaves esperadas.** Variável nova entra
  com placeholder no mesmo commit que a usa.
- **Admin inicial**: `ADMIN_EMAIL` / `ADMIN_PASSWORD` no `.env`. A senha é
  **hasheada** ao gravar, mas fica em **texto puro no `.env`** até a migration rodar:
  - Defina uma **senha forte** antes do primeiro `migrate` (o fallback `change-me` é inseguro).
  - Em banco já migrado, troque a senha do admin manualmente (ex.: `php artisan tinker`).
  - **Nunca** commitar um `.env` com senha real.
- **`APP_KEY`** rotacionável apenas em incidente confirmado (invalida sessões).
- **Banco**: `DB_CONNECTION=mysql` (MariaDB/MySQL) ou `pgsql` (PostgreSQL). Nunca `sqlite`.

---

## 8. Rate Limiting (lacuna prioritária)

> ⚠️ **Estado atual:** **não há** rate limiting nas rotas de autenticação. Isso
> deixa `/login`, `/register`, `/forgot-password` e `/reset-password` expostos a
> brute-force e abuso automatizado. **Implementar é prioridade de segurança.**

Recomendação:

| Rota | Limite sugerido | Chave |
|---|---|---|
| `POST /login` | 5/min | IP + e-mail |
| `POST /register` | 5/min | IP |
| `POST /forgot-password` | 3/min | IP + e-mail |
| `POST /reset-password` | 5/min | IP |

Definir limiters em `AppServiceProvider::boot()` via `RateLimiter::for(...)` e
aplicar `->middleware('throttle:nome')` nas rotas. Resposta ao exceder: **429**.

---

## 9. Upload de Arquivos (avatar)

- Aceitar **apenas** imagem: mime `image/jpeg`, `image/png`, `image/webp`.
- Validar `isValid()` e **tamanho ≤ 2 MB**.
- Armazenar no disco `public` com nome gerado pelo framework (`store('avatars','public')`)
  — nunca usar o nome original enviado pelo cliente.
- Ao trocar/remover, apagar o arquivo anterior.
- Servir via rota controlada (`/profile/avatar/{user}`), com `404` se não existir.

---

## 10. Tratamento de Erros

- **`APP_DEBUG=false` em PROD.** Sempre.
- Stacktraces, queries e valores de `.env` **nunca** chegam ao navegador.
- Mensagens de auth não distinguem casos (ver §5).
- Logar falhas relevantes (login falho, ações de admin) **sem** PII desnecessária.

---

## 11. LGPD — Dados de uma criança

A aplicação **armazena PII de crianças**: nome, sobrenome, e-mail, telefone, bio e
**foto de perfil (avatar)**. O cuidado é redobrado — são dados de menores.

- **Finalidade**: acompanhamento educacional/parental do progresso. Nada além disso.
- **Minimização**: não coletar dado além do necessário. Telefone, bio e avatar são
  **opcionais**. Não há geolocalização nem rastreio.
- **Modo convidado**: permite uso **sem** coletar qualquer PII (tudo fica na sessão).
- **Acesso restrito**: dados pessoais só visíveis para o próprio usuário e para o admin.
- **Retenção / exclusão**: exclusão lógica preserva histórico para auditoria;
  exclusão definitiva e exportação podem ser feitas mediante solicitação do responsável.
- **Avatar**: é foto de criança — tratar como dado sensível; servir apenas a quem tem acesso.
- **Sem compartilhamento** com terceiros. Sem analytics externo / cookies de rastreio.
- **E-mail transacional**: links de recuperação de senha não devem vazar tokens em logs.

Responsável pelos dados: **Samir Hanna Verza** (pai/responsável).

---

## 12. Manutenção de Dependências

- **`composer audit`** — antes de cada deploy. `high`/`critical` bloqueia.
- **`npm audit`** — idem para assets de produção.
- **`composer.lock` e `package-lock.json` versionados.**
- **Updates major** (Laravel/Livewire) nunca em hotfix — planejar janela e testar.

---

## 13. Resposta a Incidentes

1. **Conter.** `php artisan down`.
2. **Revogar.** Trocar senha do admin; limpar sessões (`DELETE FROM sessions`).
3. **Rotar segredos.** `APP_KEY` somente em incidente grave (invalida sessões).
4. **Snapshot do banco.** `mariadb-dump`/`pg_dump` da hora do incidente, guardado à parte.
5. **Examinar logs.** `storage/logs/laravel.log` — IPs anômalos, logins em massa,
   floods em cadastro/recuperação, ações de admin inesperadas (`admin_user_actions`).
6. **Comunicar.** Notificar o responsável (Samir).
7. **Postmortem.** `.md` com timeline, causa raiz e ações.
8. **Patch.** Corrigir e, se virar regra, atualizar este documento.

---

## 14. Checklist Rápido (pré-commit)

- [ ] Queries usam binding (Eloquent/Query Builder)? Nenhum `whereRaw` sem bind?
- [ ] Outputs escapados com `{{ }}` (inclusive nome/bio/e-mail no admin e ranking)?
- [ ] `@csrf` em todos os formulários?
- [ ] `is_admin` **não** vem de input não confiável?
- [ ] Validação no servidor (e-mail, tamanho de senha, mime/tamanho de avatar)?
- [ ] Ações sobre usuário checam dono/estado e exigem justificativa (admin)?
- [ ] Senha do admin definida forte no `.env` (nunca `change-me`, nunca commitada)?
- [ ] Dados sensíveis/PII **não** estão sendo logados?
- [ ] Banco é MariaDB/MySQL ou PostgreSQL (**nunca SQLite**)?
- [ ] `.env.example` atualizado com variável nova?
- [ ] `APP_DEBUG=false` em PROD?
- [ ] (Quando implementado) `throttle` ativo nas rotas de auth?

---

**Responsável:** Samir Hanna Verza

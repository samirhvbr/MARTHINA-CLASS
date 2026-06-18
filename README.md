# 🎓 Marthina Learning

Plataforma educacional gamificada para crianças. Ensina **vocabulário de inglês** e aplica **quizzes por matéria** de forma lúdica, com sistema de pontos, XP, troféus e ranking para manter a criança engajada.

A interface é colorida e amigável (fontes _Baloo 2_ / _Nunito_, paleta suave), pensada para o público infantil.

---

> **Ver também:** [CLAUDE.md](CLAUDE.md) (convenções de código e guia para agentes) · [SECURITY_GUIDELINES.md](SECURITY_GUIDELINES.md) (segurança — revisar a cada mudança de stack ou novo input de usuário) · [version.md](version.md) (versão e changelog).

## 🔖 Versão (`version.md`)

A versão do projeto fica em [`version.md`](version.md) na raiz, lida em runtime via `config('app.version')`. Formato `X.Y.Z`:

- **X** — versão estável final (manual)
- **Y** — mudança estrutural significativa (manual)
- **Z** — incremento por nova tela, nova tabela, mudança de layout, regra de negócio ou feature

Commits seguem o formato `X.Y.Z - descrição` (em português).

---

## ✨ Funcionalidades

- 📚 **Vocabulário de inglês** — palavras com imagem, organizadas por categoria.
- 🧠 **Quizzes** — perguntas de múltipla escolha agrupadas por matéria (_subjects_) e categoria.
- ⭐ **Pontuação & XP** — registra acertos, pontos e experiência a cada atividade.
- 🏆 **Ranking & troféus** — classificação entre os jogadores e troféus por desempenho (ouro/prata/bronze).
- 👤 **Perfis** — avatar, nome de exibição e progresso individual.
- 🙋 **Modo convidado** — joga sem criar conta; o progresso fica na sessão.
- 🔐 **Autenticação completa** — registro (com proteção anti-bot), login e recuperação de senha.
- 🛠️ **Painel administrativo** — gestão de usuários e conteúdo, com auditoria das ações do admin.

---

## 🧰 Tecnologias

| Camada | Tecnologia |
|--------|------------|
| Backend | PHP 8.2+ · [Laravel 12](https://laravel.com) |
| Componentes reativos | [Livewire 4](https://livewire.laravel.com) |
| Front-end / build | [Vite 7](https://vitejs.dev) · [Tailwind CSS 4](https://tailwindcss.com) |
| UI auxiliar (CDN) | Bootstrap 5 · Font Awesome 6 |
| Banco de dados | **MariaDB / MySQL** (PostgreSQL aceito) |

> 🛢️ **Banco de dados:** sempre **MariaDB/MySQL** (ou PostgreSQL). **SQLite não é usado em nenhum contexto**, nem em desenvolvimento.

---

## 📋 Pré-requisitos

- **PHP** >= 8.2 (com as extensões padrão do Laravel)
- **Composer**
- **Node.js** + **npm**
- **MariaDB** (ou MySQL / PostgreSQL) em execução

---

## 🚀 Instalação

### 1. Crie o banco de dados

```sql
CREATE DATABASE marthina CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'marthina'@'localhost' IDENTIFIED BY 'senha-forte';
GRANT ALL PRIVILEGES ON marthina.* TO 'marthina'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Dependências e ambiente

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

### 3. Configure o `.env`

Ajuste as credenciais do banco e o admin inicial **antes** de migrar:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=marthina
DB_USERNAME=marthina
DB_PASSWORD=senha-forte

ADMIN_EMAIL=admin@marthina.com.br
ADMIN_PASSWORD=defina-uma-senha-forte
```

### 4. Migrations, seeders e assets

```bash
php artisan migrate --seed
npm run build
```

> 💡 O script `composer setup` automatiza dependências, `.env`, key, migrations e build — **configure o banco no `.env` antes de rodá-lo**.

---

## 🧑‍💻 Desenvolvimento

Para subir tudo de uma vez (servidor, fila, logs e Vite em paralelo):

```bash
composer dev
```

Ou separadamente:

```bash
php artisan serve     # http://localhost:8000
npm run dev           # Vite em modo watch
```

---

## 🗄️ Banco de dados & Seeders

As migrations criam as tabelas de usuários, palavras (`eng_words`), categorias, matérias, perguntas/opções, pontuações, resultados de quiz e auditoria de ações do admin.

Seeders disponíveis (`database/seeders/`):

- `SubjectSeeder` — matérias
- `CategorySeeder` — categorias
- `WordSeeder` — vocabulário de inglês
- `QuestionSeeder` — perguntas dos quizzes

Rodar todos de uma vez:

```bash
php artisan db:seed
```

---

## 🔐 Acesso administrativo

Uma migration cria um usuário administrador inicial. As credenciais são lidas do `.env`, então defina-as **antes** de rodar as migrations:

```env
ADMIN_EMAIL=admin@marthina.com.br
ADMIN_PASSWORD=suaSenhaForte
```

> ⚠️ Se `ADMIN_PASSWORD` não for definida, a migration usa o fallback inseguro `change-me`. **Defina uma senha real antes de qualquer uso.** A senha é armazenada com hash; o valor em texto puro existe apenas no `.env` (que **nunca** é commitado). Em um banco já migrado, atualize a senha do admin manualmente (ex.: `php artisan tinker`).

O painel fica acessível em `/admin` para usuários com a flag `is_admin`. Toda ação destrutiva (bloquear, excluir, restaurar) exige justificativa e fica registrada.

---

## 🧪 Testes

```bash
composer test
# ou
php artisan test
```

---

## 📁 Estrutura (resumo)

```
app/
  Livewire/        # Componente Vocabulary (interatividade reativa)
  Models/          # User, Word, Category, Subject, Question, QuestionOption,
                   # Score, QuizResult, AdminUserAction
  Http/            # Controller base (a lógica vive em routes/web.php)
database/
  migrations/      # Evolução do schema
  seeders/         # Conteúdo inicial (matérias, palavras, perguntas)
resources/
  views/           # Telas: home, quiz, vocabulary, ranking, profile, admin, auth/...
  css/ · js/       # Entradas do Vite
routes/
  web.php          # Rotas da aplicação (lógica em closures)
public/
  assets/marthina-theme/   # Imagens do tema
```

> ℹ️ Hoje a maior parte da lógica de rotas vive em `routes/web.php` (closures), incluindo o fluxo de convidado, autenticação e administração. Código novo deve seguir a convenção-alvo descrita em [CLAUDE.md](CLAUDE.md) (controllers finos + Form Requests).

---

## 📄 Licença

Projeto pessoal/educacional. Construído sobre o Laravel, que é open-source sob licença [MIT](https://opensource.org/licenses/MIT).

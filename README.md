# 🎓 Marthina Learning

Gamified educational platform for children. It teaches **English vocabulary** and applies **subject-based quizzes** in a playful way, with a system of points, XP, trophies and a ranking to keep the child engaged.

The interface is colorful and friendly (_Baloo 2_ / _Nunito_ fonts, a soft palette), designed for a young audience.

---

> **See also:** [CLAUDE.md](CLAUDE.md) (code conventions and agent guide) · [SECURITY_GUIDELINES.md](SECURITY_GUIDELINES.md) (security — review on every stack change or new user input) · [version.md](version.md) (version and changelog).

## 🔖 Version (`version.md`)

The project version lives in [`version.md`](version.md) at the root, read at runtime via `config('app.version')`. Format `X.Y.Z`:

- **X** — final stable version (manual)
- **Y** — significant structural change (manual)
- **Z** — increment for a new screen, new table, layout change, business rule or feature

Commits follow the format `X.Y.Z - description` (in Portuguese).

---

## ✨ Features

- 📚 **English vocabulary** — words with an image, organized by category.
- 🧠 **Quizzes** — multiple-choice questions grouped by subject (_subjects_) and category.
- ⭐ **Scoring & XP** — records correct answers, points and experience on each activity.
- 🏆 **Ranking & trophies** — ranking among players and trophies by performance (gold/silver/bronze).
- 👤 **Profiles** — avatar, display name and individual progress.
- 🙋 **Guest mode** — play without creating an account; progress is kept in the session.
- 🔐 **Full authentication** — registration (with anti-bot protection), login and password recovery.
- 🛠️ **Admin panel** — user and content management, with auditing of the admin's actions.

---

## 🧰 Technologies

| Layer | Technology |
|--------|------------|
| Backend | PHP 8.2+ · [Laravel 12](https://laravel.com) |
| Reactive components | [Livewire 4](https://livewire.laravel.com) |
| Front-end / build | [Vite 7](https://vitejs.dev) · [Tailwind CSS 4](https://tailwindcss.com) |
| Auxiliary UI (CDN) | Bootstrap 5 · Font Awesome 6 |
| Database | **MariaDB / MySQL** (PostgreSQL accepted) |

> 🛢️ **Database:** always **MariaDB/MySQL** (or PostgreSQL). **SQLite is not used in any context**, not even in development.

---

## 📋 Prerequisites

- **PHP** >= 8.2 (with Laravel's standard extensions)
- **Composer**
- **Node.js** + **npm**
- **MariaDB** (or MySQL / PostgreSQL) running

---

## 🚀 Installation

### 1. Create the database

```sql
CREATE DATABASE marthina CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'marthina'@'localhost' IDENTIFIED BY 'senha-forte';
GRANT ALL PRIVILEGES ON marthina.* TO 'marthina'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Dependencies and environment

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

### 3. Configure `.env`

Adjust the database credentials and the initial admin **before** migrating:

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

### 4. Migrations, seeders and assets

```bash
php artisan migrate --seed
npm run build
```

> 💡 The `composer setup` script automates dependencies, `.env`, key, migrations and build — **configure the database in `.env` before running it**.

---

## 🧑‍💻 Development

To bring everything up at once (server, queue, logs and Vite in parallel):

```bash
composer dev
```

Or separately:

```bash
php artisan serve     # http://localhost:8000
npm run dev           # Vite in watch mode
```

---

## 🗄️ Database & Seeders

The migrations create the tables for users, words (`eng_words`), categories, subjects, questions/options, scores, quiz results and audit of admin actions.

Available seeders (`database/seeders/`):

- `SubjectSeeder` — subjects
- `CategorySeeder` — categories
- `WordSeeder` — English vocabulary
- `QuestionSeeder` — quiz questions

Run them all at once:

```bash
php artisan db:seed
```

---

## 🔐 Administrative access

A migration creates an initial administrator user. The credentials are read from `.env`, so set them **before** running the migrations:

```env
ADMIN_EMAIL=admin@marthina.com.br
ADMIN_PASSWORD=suaSenhaForte
```

> ⚠️ If `ADMIN_PASSWORD` is not set, the migration uses the insecure fallback `change-me`. **Set a real password before any use.** The password is stored hashed; the plaintext value exists only in `.env` (which is **never** committed). On an already-migrated database, update the admin password manually (e.g., `php artisan tinker`).

The panel is accessible at `/admin` for users with the `is_admin` flag. Every destructive action (block, delete, restore) requires a justification and is logged.

---

## 🧪 Tests

```bash
composer test
# or
php artisan test
```

---

## 📁 Structure (summary)

```
app/
  Livewire/        # Vocabulary component (reactive interactivity)
  Models/          # User, Word, Category, Subject, Question, QuestionOption,
                   # Score, QuizResult, AdminUserAction
  Http/            # Base controller (the logic lives in routes/web.php)
database/
  migrations/      # Schema evolution
  seeders/         # Initial content (subjects, words, questions)
resources/
  views/           # Screens: home, quiz, vocabulary, ranking, profile, admin, auth/...
  css/ · js/       # Vite entries
routes/
  web.php          # Application routes (logic in closures)
public/
  assets/marthina-theme/   # Theme images
```

> ℹ️ Today most of the routing logic lives in `routes/web.php` (closures), including the guest flow, authentication and administration. New code should follow the target convention described in [CLAUDE.md](CLAUDE.md) (thin controllers + Form Requests).

---

## 📄 License

Personal/educational project. Built on Laravel, which is open-source under the [MIT](https://opensource.org/licenses/MIT) license.

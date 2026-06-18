# 🎓 Marthina Learning

Plataforma educacional gamificada para crianças. Ensina **vocabulário de inglês** e aplica **quizzes por matéria** de forma lúdica, com sistema de pontos, XP e ranking para manter a criança engajada.

A interface é colorida e amigável (fontes _Baloo 2_ / _Nunito_, paleta suave), pensada para o público infantil.

---

## ✨ Funcionalidades

- 📚 **Vocabulário de inglês** — palavras com imagem, organizadas por categoria.
- 🧠 **Quizzes** — perguntas de múltipla escolha agrupadas por matéria (_subjects_) e categoria.
- ⭐ **Pontuação & XP** — registra acertos, pontos e experiência a cada atividade.
- 🏆 **Ranking** — classificação entre os jogadores.
- 👤 **Perfis** — avatar, nome de exibição e progresso individual.
- 🙋 **Modo convidado** — é possível jogar sem criar conta; o progresso fica na sessão.
- 🔐 **Autenticação completa** — registro, login e recuperação de senha.
- 🛠️ **Painel administrativo** — gestão de usuários e conteúdo, com registro de ações do admin.

---

## 🧰 Tecnologias

| Camada | Tecnologia |
|--------|------------|
| Backend | PHP 8.2+ · [Laravel 12](https://laravel.com) |
| Componentes reativos | [Livewire 4](https://livewire.laravel.com) |
| Front-end / build | [Vite 7](https://vitejs.dev) · [Tailwind CSS 4](https://tailwindcss.com) |
| UI auxiliar (CDN) | Bootstrap 5 · Font Awesome 6 |
| Banco de dados | SQLite (padrão) |

---

## 📋 Pré-requisitos

- **PHP** >= 8.2 (com as extensões padrão do Laravel)
- **Composer**
- **Node.js** + **npm**

---

## 🚀 Instalação

### Opção rápida (script `setup`)

O `composer.json` já traz um script que executa toda a configuração inicial:

```bash
composer setup
```

Ele instala dependências, cria o `.env`, gera a chave da aplicação, roda as migrations e compila os assets.

### Passo a passo (manual)

```bash
# 1. Dependências PHP e JS
composer install
npm install

# 2. Ambiente
cp .env.example .env
php artisan key:generate

# 3. Banco de dados (SQLite)
touch database/database.sqlite
php artisan migrate --seed

# 4. Assets
npm run build
```

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

O projeto usa **SQLite** por padrão (`database/database.sqlite`). As migrations criam as tabelas de usuários, palavras (`eng_words`), categorias, matérias, perguntas/opções, pontuações e resultados de quiz.

Seeders disponíveis (`database/seeders/`):

- `SubjectSeeder` — matérias
- `CategorySeeder` — categorias
- `WordSeeder` — vocabulário de inglês
- `QuestionSeeder` — perguntas dos quizzes

Rodar todos de uma vez:

```bash
php artisan db:seed
```

> 💡 O banco SQLite é ignorado pelo Git (`database/.gitignore`), então cada ambiente mantém seus próprios dados.

---

## 🔐 Acesso administrativo

Uma migration cria um usuário administrador inicial. As credenciais são lidas do `.env` (com valores de fallback), então defina-as **antes** de rodar as migrations:

```env
ADMIN_EMAIL=admin@marthina.com.br
ADMIN_PASSWORD=suaSenhaForte
```

> ⚠️ Se `ADMIN_PASSWORD` não for definida, a migration usa o fallback `change-me`. **Defina uma senha real antes de qualquer uso em produção.** Em um banco já migrado, atualize a senha do admin manualmente (ex.: via `php artisan tinker`).

O painel fica acessível em `/admin` para usuários com a flag `is_admin`.

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
  Models/          # User, Word, Category, Subject, Question, Score, QuizResult, ...
  Observers/
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

> ℹ️ A maior parte da lógica de rotas vive em `routes/web.php` (closures), incluindo o fluxo de convidado, autenticação e administração.

---

## 📄 Licença

Projeto pessoal/educacional. Construído sobre o Laravel, que é open-source sob licença [MIT](https://opensource.org/licenses/MIT).

# Versão — Marthina Learning

**Versão atual:** `0.1.1`

> Esta versão é a fonte da verdade do projeto e é lida em runtime via
> `config('app.version')` — a aplicação extrai o **primeiro número semver
> (`X.Y.Z`)** encontrado neste arquivo. Mantenha a linha **"Versão atual"**
> sempre como a primeira ocorrência de um número de versão.

---

## 1. Convenção de Versionamento (`X.Y.Z`)

Padrão semântico simplificado, herdado do guia de projetos do Samir e adaptado
para esta plataforma educacional.

| Componente | Significado | Como sobe |
|---|---|---|
| **X** | Versão estável final / release público | Manual |
| **Y** | Mudança estrutural (nova área, refatoração grande, nova integração) | Manual |
| **Z** | Incremento a cada entrega (ver gatilhos) | A cada entrega |

### Gatilhos de bump do `Z`

Incremente o `Z` (e registre no changelog) sempre que:

- Criar uma **tela / view** nova (quiz, vocabulário, perfil, admin, etc.)
- Criar uma **tabela / migration** nova
- **Modificar layout** (estrutura visual de uma tela)
- **Renomear** botão, label, rota nomeada ou coluna
- Alterar **regra de negócio** (pontuação, XP, troféus, fluxo de convidado, quiz)
- Alterar **configuração de segurança** (rate limit, CSRF, headers, auth, admin)

> Correções de texto, comentários e formatação (`pint`) **não** exigem bump.

---

## 2. Formato de Commit Obrigatório

```
X.Y.Z - Descrição curta em português
```

Exemplo:

```bash
git commit -m "0.1.1 - Adiciona rate limit no login e na recuperacao de senha"
```

O bump do `version.md` entra em **um único commit** por entrega (o primeiro da
entrega). Commits adicionais da mesma entrega repetem a versão sem novo bump.

---

## 3. Changelog

> Ordem decrescente (mais recente no topo). Cada entrada lista as mudanças e os
> gatilhos que justificaram o bump.

### `0.1.0` — 2026-06-18 — Padronização de documentação e versionamento

Primeira entrega da plataforma sob o **padrão de projetos do Samir**. O código
da aplicação já existia (auth, modo convidado, quizzes, vocabulário, ranking e
painel administrativo); esta entrega formaliza a documentação e o versionamento.

**Documentação**
- `version.md` (este arquivo) — convenção de versionamento + changelog, lido em
  runtime via `config('app.version')`.
- `CLAUDE.md` — guia operacional para agentes de IA.
- `SECURITY_GUIDELINES.md` — diretrizes de segurança adaptadas (auth de usuário
  final + painel admin + dados de criança / LGPD).
- `README.md` — atualizado para o padrão (links, seção de versão, banco).

**Banco de dados**
- Padrão de banco alinhado: **MariaDB/MySQL (ou PostgreSQL) — nunca SQLite**.
  `.env.example` e `config/database.php` passam a usar `mysql` por padrão.

_Gatilhos:_ baseline de documentação/versionamento e mudança de configuração de
banco (estrutural).

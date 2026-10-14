# PROMPTATLAS — DEPLOYMENT AND ENVIRONMENT

Phase 02 created the local development environment. Staging and production are Phase 22;
nothing in this file describes a live server yet.

---

## 1. Local development environment

Selected environment: **XAMPP**.

LocalWP is not installed on this machine, so the Phase 02 preference for LocalWP did not apply.
XAMPP was already installed, Apache and MariaDB were already running, WP-CLI was already present
at `C:\xampp\wp-cli.phar`, and other projects already live in `C:\xampp\htdocs`. Introducing a
second environment would have meant two PHP versions, two databases, and two sets of paths for
one project.

Only one environment runs this project. Do not create a LocalWP copy of PromptAtlas alongside it.

### Environment facts

| Fact | Value |
|---|---|
| Operating system | Windows 11 Pro 10.0.26100 |
| Web server | Apache 2.4.58 (Win64, Apache Lounge VS17) |
| PHP version | 8.2.12 (ZTS, VC2019, x64) |
| **PHP executable** | `C:\xampp\php\php.exe` |
| `php.ini` | `C:\xampp\php\php.ini` |
| Database | MariaDB 10.4.32 |
| MySQL binaries | `C:\xampp\mysql\bin\` (**not** on the system `PATH`) |
| WordPress version | 7.1.3 |
| **Site URL** | `http://localhost/promptmarket` |
| **WordPress root** | `C:\xampp\htdocs\promptmarket` |
| **Theme directory** | `C:\xampp\htdocs\promptmarket\wp-content\themes\promptatlas` |
| Database name | `promptatlas` |
| Database user | `promptatlas_usr` (dedicated; **not** `root`) |
| Table prefix | `wp_` |
| WP-CLI | 2.12.0 — `C:\xampp\wp-cli.phar` |

### Credentials

Local credentials live **outside the repository**, in:

```
C:\Users\Syed Safwan\promptatlas-local-credentials.txt
```

That file holds the WordPress administrator login and the database password. It is never
committed; `.gitignore` also excludes `*-credentials.txt` and `wp-config.php` as a second line
of defence. These are local-only credentials and must never be reused on staging or production.

WordPress runs as the dedicated database user `promptatlas_usr`, scoped to the `promptatlas`
database only — not as `root`.

---

## 2. Running the site locally

1. Start **Apache** and **MySQL** from the XAMPP Control Panel (`C:\xampp\xampp-control.exe`).
2. Open `http://localhost/promptmarket`.
3. Admin: `http://localhost/promptmarket/wp-admin`, credentials in the file above.
4. Open the VS Code workspace at `C:\xampp\htdocs\promptmarket`.

---

## 3. Running WP-CLI on this machine

WP-CLI is a `.phar`, so it is invoked through the PHP executable.

**PowerShell (recommended):**

```powershell
C:\xampp\php\php.exe C:\xampp\wp-cli.phar --path="C:\xampp\htdocs\promptmarket" <command>
```

**Git Bash — read this before using it.** Git Bash's MSYS path conversion silently rewrites
arguments that look like POSIX paths. During Phase 02 it corrupted both the site URL and the
permalink structure (see `PROGRESS.md`). Any argument starting with `/`, and any URL with a
path, must be protected:

```bash
export MSYS_NO_PATHCONV=1
export MSYS2_ARG_CONV_EXCL='*'
# and reference the phar with a Windows-style path:
C:/xampp/php/php.exe C:/xampp/wp-cli.phar rewrite structure '/%postname%/'
```

### `wp db` subcommands

`wp db check`, `wp db query`, `wp db export` and `wp db import` shell out to `mysql.exe` and
`mysqlcheck.exe`, which are not on the `PATH`. Either add `C:\xampp\mysql\bin` to `PATH`, or
call the binaries directly:

```bash
C:/xampp/mysql/bin/mysql.exe -u root promptatlas -e "SELECT 1;"
C:/xampp/mysql/bin/mysqlcheck.exe -u root promptatlas
C:/xampp/mysql/bin/mysqldump.exe -u root promptatlas > backup.sql
```

### Do not update core with `wp core download --force`

The WordPress 7.1.3 tarball contains a file whose name ends in a dot
(`wp-includes/php-ai-client/src/Providers/Http/Contracts/WithRequestAuthenticationInterface.`).
Windows cannot create such a file, so tarball extraction fails and WP-CLI's `tar` fallback is
not available here. Update core through the WordPress admin updater, or with the `.zip` package.

---

## 4. Local WordPress configuration

Set in `wp-config.php` for development only. Each must be reviewed before any deployment:

| Constant | Local value | Production expectation |
|---|---|---|
| `WP_ENVIRONMENT_TYPE` | `local` | `production` |
| `WP_DEBUG` | `true` | `false` |
| `WP_DEBUG_LOG` | `true` | `false`, or a path outside the web root |
| `WP_DEBUG_DISPLAY` | `false` | `false` — never show errors to visitors |
| `SCRIPT_DEBUG` | `true` | `false` |
| `DISALLOW_FILE_EDIT` | `true` | `true` |
| `WP_POST_REVISIONS` | `10` | `10` |
| `EMPTY_TRASH_DAYS` | `30` | `30` |

Search engine visibility is **discouraged** locally (`blog_public = 0`). Enabling indexing at
launch is tracked in `LAUNCH_CHECKLIST.md`.

---

## 5. Backups taken

| What | Where | When |
|---|---|---|
| MariaDB `mysql` system tables (92 files, 13 MB) before grant-table repair | `C:\xampp\backups\mysql-systables-20261014\` | 2026-10-14 |

The database is never committed to Git. Phase 22 defines the real backup strategy.

---

## 6. Not yet decided

- Hosting provider, plan and region — blocked on the Phase 01 audience/geography decision.
- Domain name — Phase 01 item 1, pending.
- Deployment method, staging environment, and the production database workflow — **Phase 22**.
- Repository boundary and what actually gets deployed — **Phase 03**.

Do not treat any of the above as settled. There is no staging or production environment yet.

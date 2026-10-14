# PHASE 02 — ENVIRONMENT AUDIT AND LOCAL WORDPRESS SETUP

**Prerequisites:** Phase 01 decisions 2, 3, 4 and 8 resolved.
**Produces:** a working local WordPress site and the recorded environment facts.
**Do not:** build the theme, design system, or any templates in this phase.

My environment:
- Operating system: Windows
- Editor: VS Code
- XAMPP and/or LocalWP may already be installed
- WordPress has not yet been installed for this project
- The final website will use a custom WordPress theme

## Goal

Prepare a working local WordPress development site with a safe, repeatable setup. Automate as much as possible without bypassing Windows security or requiring unnecessary manual work.

## Step 1: Inspect before installing

Inspect:
- Current workspace path
- Git availability
- PHP availability and version
- Composer availability if relevant
- Node.js availability only if a real requirement exists
- Whether XAMPP is installed and Apache/MySQL are available
- Whether LocalWP is installed
- Whether an existing WordPress installation exists
- Whether any existing project files must be preserved

Use non-destructive checks first. Do not assume a program exists merely because it was mentioned.

## Step 2: Choose one local environment

Prefer LocalWP if it is already installed and functional because it manages WordPress, PHP, the web server, and the database conveniently.

If LocalWP is not available, inspect XAMPP and determine whether it is suitable.

Do not run a LocalWP-managed site and a separate XAMPP WordPress site for the same project. Do not change ports or stop unrelated services without understanding the impact.

If neither environment is usable:
- Explain the recommended installation.
- Use official vendor sources only.
- Automate downloads or setup only when the required tools and permissions are available.
- Request user approval for installer execution, administrator permissions, or security prompts when necessary.
- Never bypass Windows security protections.

## Step 3: Install WordPress

Create a local site named PromptAtlas.

Use a strong, unique local administrator username and password. Do not use `admin` as the username if avoidable. Never print the password in the terminal report or commit it to Git.

If a browser-based installation step, password entry, or GUI confirmation is required, tell me the exact action needed. Do not claim WordPress is installed until you verify the frontend and `/wp-admin`.

Configure:
- Site title: PromptAtlas
- Language: per the Phase 01 language decision
- Permalink structure: Post name
- A local-only development URL
- Search engine visibility discouraged during development, with a documented reminder in `LAUNCH_CHECKLIST.md` to enable indexing before launch
- Comments enabled or disabled per the Phase 01 decision

## Step 4: Prepare the workspace

Locate the actual WordPress installation and its theme directory.

Create or verify:
`wp-content/themes/promptatlas/`

Identify the exact workspace path for VS Code. Do not place theme files in the WordPress root by mistake.

Record the environment facts that later phases depend on:
- PHP version and the path to the PHP executable
- WordPress version
- MySQL or MariaDB version
- Local site URL
- WordPress root directory
- Theme directory
- Whether WP-CLI is available

Write these into `PROGRESS.md` and `DEPLOYMENT.md`. Several later phases will need the PHP executable path for syntax checks.

Do not set up Git in this phase. Phase 03 owns version control, so that the repository boundary and ignore rules are decided deliberately rather than improvised.

## Step 5: Verification

Verify:
- WordPress frontend loads successfully
- WordPress admin login page loads
- Dashboard is accessible after login
- Database connection works
- Correct theme directory has been identified
- PHP and WordPress versions are recorded
- No unrelated project files were overwritten

## Completion report

Report:
1. Selected local environment and why
2. WordPress URL
3. WordPress installation directory
4. Theme directory
5. PHP, WordPress, and database versions
6. Whether WP-CLI is available
7. Commands actually run and their results
8. Any manual action still required
9. Security or compatibility concerns
10. Confirmation that Phase 03 is next

Do not invent URLs, credentials, test results, or successful installation status.

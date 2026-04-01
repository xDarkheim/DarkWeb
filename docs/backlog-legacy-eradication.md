# DarkCore — Legacy Eradication Backlog

> Internal backlog for systematically eradicating legacy patterns from the project.
> Start date: 2026-04-01.

## Goal

Bring the project to a state where:

- code under `src/` does not read PHP superglobals directly;
- the web/admin/cron/install flow is not tied to include-driven procedural execution;
- data access is not spread across controllers/services via raw SQL;
- runtime and config are not managed through global constants / `$GLOBALS` / static context where explicit dependencies can be passed instead;
- views stay dumb and receive a ready-made view-model;
- legacy islands in `public/install/`, `includes/cron/`, and `views/admincp/mconfig/` are either removed or reduced to thin adapters.

## Completion criteria

We consider this program complete when all of the following are true:

- [ ] There are no direct `$_GET`, `$_POST`, `$_REQUEST`, `$_SESSION`, or `$_SERVER` reads in `src/` outside runtime adapters.
- [ ] There are no direct `Connection::Database()` calls from controller-level code under `src/Application/`.
- [ ] Large SQL queries have been extracted into repositories/query services with explicit APIs.
- [ ] `public/install/` has been moved away from the step-include model to a controller/action-orchestrated flow, or isolated as a dedicated adapter with minimal legacy surface.
- [ ] `includes/cron/*.php` have been replaced with class-based jobs or thin shim files on top of them.
- [ ] `views/admincp/mconfig/*.php` are no longer pulled in through dynamic `include` from `modulesmanager`.
- [ ] New features do not use `BootstrapContext` as a service locator where dependencies can be passed explicitly.
- [ ] `$GLOBALS` usage in production code has been eliminated.
- [ ] Module configuration and runtime schema no longer depend on ad hoc mixing of JSON/XML/PHP constants without a single access strategy.

## Migration principles

1. **Do not break behavior.** First wrap legacy in adapters, then clean up the implementation.
2. **Migrate vertically.** It is better to finish one zone end-to-end than to spread partial refactors everywhere.
3. **Do not create new legacy.** Any new logic must go through `src/`, without procedural include files.
4. **Boundary first, internals second.** Remove superglobals / globals / includes at the execution boundaries first.
5. **One source of truth.** Config and runtime context should be read through explicit services.
6. **Every step is closed by tests.** Routing, runtime adapters, SQL extraction, cron jobs.

## Priority legend

- **P0** — architectural blocker that continues to leak legacy into new code.
- **P1** — large legacy zone with high ROI.
- **P2** — important cleanup after the boundary is aligned.
- **P3** — polish / remaining transitional areas.

## Epics

### EPIC A — Runtime boundary cleanup (`P0`)

**Problem:** direct superglobal usage still exists in `src/`, even though runtime adapters are already available.

**Goal:** all runtime state in `src/` flows through abstractions (`QueryStore`, `PostStore`, `RequestStore`, `SessionStore`, `ServerContext`).

**Areas:**

- `src/Application/News/NewsController.php`
- `src/Application/Rankings/RankingsController.php`
- `src/Application/Usercp/UsercpController.php`
- `src/Application/Usercp/Subpage/MyEmailSubpageController.php`
- `src/Application/Website/ContactController.php`
- `src/Application/Profile/ProfilePlayerSubpageController.php`
- `src/Application/Theme/Layout/DefaultThemeLayoutBuilder.php`
- other superglobal usages in `src/`

**Definition of Done:**

- [ ] Controllers and layout builders accept runtime adapters via constructor injection.
- [ ] There are no new direct superglobal reads in `src/`.
- [ ] Unit tests have been added/updated to verify behavior through injected stores.

---

### EPIC B — Data access extraction (`P0`)

**Problem:** SQL and `Connection::Database()` are embedded directly in the application/service layer.

**Goal:** extract reads/writes into repositories/query services.

**Areas:**

- `src/Application/Rankings/RankingsService.php`
- `src/Application/Usercp/Subpage/BuyZenSubpageController.php`
- `src/Application/Usercp/Subpage/MyAccountSubpageController.php`
- other classes under `src/Application/*` that use `Connection::Database()`
- procedural SQL in `includes/cron/*.php`

**Definition of Done:**

- [ ] Controllers do not contain raw SQL.
- [ ] Large SQL queries are encapsulated in dedicated classes.
- [ ] Tests exist for extracted repositories / query services.
- [ ] Repository names reflect the domain, not the transport layer.

---

### EPIC C — Cron migration (`P1`)

**Problem:** the new CLI entrypoint already exists, but jobs are still executed through included procedural files.

**Goal:** migrate cron execution to class-based jobs with a compatible transitional layer.

**Areas:**

- `bin/cron.php`
- `src/Infrastructure/Cron/CronExecutor.php`
- `includes/cron/cron.php`
- `includes/cron/*.php`

**Definition of Done:**

- [ ] Every cron task has a class-based implementation.
- [ ] `CronExecutor` runs job classes instead of PHP files.
- [ ] `includes/cron/*.php` are either removed or converted into thin compatibility shim files.
- [ ] Tests exist for schedule resolution and dispatch.

---

### EPIC D — Installer isolation / rewrite (`P1`)

**Problem:** `public/install/` still behaves like a separate legacy application.

**Goal:** minimize the installer's legacy surface or move it to a controller-based flow.

**Areas:**

- `public/install/install.php`
- `public/install/loader.php`
- `public/install/definitions.php`
- `public/install/install_*.php`

**Definition of Done:**

- [ ] There is no switch/include orchestration across step files in the main flow.
- [ ] Validation and side effects are extracted from templates into handlers/controllers.
- [ ] Session and request access are encapsulated.
- [ ] The installer is either isolated as a dedicated legacy adapter or fully migrated.

---

### EPIC E — AdminCP transitional `mconfig` removal (`P1`)

**Problem:** `ModulesManagerController` still pulls config views dynamically by file path.

**Goal:** remove dynamic includes and move module settings to explicit handlers/views.

**Areas:**

- `src/Application/Admincp/Controller/Settings/ModulesManagerController.php`
- `views/admincp/modulesmanager.php`
- `views/admincp/mconfig/**/*.php`

**Definition of Done:**

- [ ] There is no `include $selectedConfigFilePath` in the AdminCP flow.
- [ ] Each configuration form is rendered from an explicit view-model.
- [ ] Special cases (`rankings`, `castlesiege`, `email`, `vote`, `downloads`) are extracted from god-controller logic.
- [ ] Tests have been added for dispatch/config-save paths.

---

### EPIC F — Config/bootstrap decoupling (`P1`)

**Problem:** runtime and schema still rely on global constants, `BootstrapContext`, and mixed JSON/XML/PHP config access.

**Goal:** gradually move to explicit config services and typed access patterns.

**Areas:**

- `src/Infrastructure/Bootstrap/AppKernel.php`
- `src/Infrastructure/Bootstrap/BootstrapContext.php`
- `config/tables.php`
- `config/tables.custom.php`
- `config/config.json`
- `config/navigation.json`
- `config/usercp-menu.json`
- `config/castle-siege.json`
- `config/email-templates.xml`
- `config/modules/**/*.xml`

**Definition of Done:**

- [ ] New code does not require new global constants.
- [ ] `BootstrapContext` is no longer the default service locator for new application code.
- [ ] There is a single strategy for reading JSON/XML/module config.
- [ ] Table/column schema constants are wrapped or exposed through a metadata provider.

---

### EPIC G — Views slimming (`P2`)

**Problem:** some templates still read runtime state directly and/or include other PHP files.

**Goal:** keep templates focused on rendering ready-to-display data only.

**Areas:**

- `public/themes/default/index.php`
- `views/admincp/layout.php`
- `views/admincp/modulesmanager.php`
- `views/admincp/mconfig/**/*.php`

**Definition of Done:**

- [ ] Views do not read `$_SESSION`/`$_REQUEST` directly.
- [ ] Templates do not accept file paths for dynamic include.
- [ ] The theme shell does not rely on `extract()`-style implicit contracts outside a controlled boundary.

---

### EPIC H — Global state cleanup (`P2`)

**Problem:** production code still relies on `$GLOBALS` in some places.

**Goal:** remove `$GLOBALS` and implicit shared state from runtime paths.

**Areas:**

- `src/Application/Profile/ProfilePlayerSubpageController.php`
- places where `BootstrapContext::runtimeState()` is only used to avoid DI
- any remaining `$GLOBALS` usages outside tests

**Definition of Done:**

- [ ] There is no `$GLOBALS` in production code.
- [ ] Shared state is passed explicitly or through a scoped service.

## Suggested order

1. **EPIC A — Runtime boundary cleanup**
2. **EPIC B — Data access extraction**
3. **EPIC E — AdminCP transitional `mconfig` removal**
4. **EPIC C — Cron migration**
5. **EPIC F — Config/bootstrap decoupling**
6. **EPIC G — Views slimming**
7. **EPIC D — Installer isolation / rewrite**
8. **EPIC H — Global state cleanup**

> Why the installer is not first: it is isolated and painful, but it contaminates the new `src/` less than superglobals + direct SQL do.

## Initial tasks

### Sprint 0 — quick high-leverage tasks

- [ ] Introduce a rule: new code in `src/` must not read superglobals directly.
- [ ] Build a baseline list of all direct superglobal usages in `src/` and record it in the issue/task list.
- [ ] Build a baseline list of all `Connection::Database()` usages in `src/`.
- [ ] Forbid new dynamic includes in `views/` and `src/`.

### Sprint 1 — targeted boundary cleanup

- [ ] Move `src/Application/Website/ContactController.php` to `PostStore`.
- [ ] Move `src/Application/Usercp/Subpage/MyEmailSubpageController.php` to `PostStore` + `SessionStore` + `ServerContext`.
- [ ] Move `src/Application/Rankings/RankingsController.php` to `RequestStore`/`QueryStore`.
- [ ] Move `src/Application/News/NewsController.php` to `SessionStore` + `QueryStore`.
- [ ] Move `src/Application/Theme/Layout/DefaultThemeLayoutBuilder.php` to a `SessionStore`-friendly contract.

### Sprint 2 — first data-extraction tasks

- [ ] Extract the SQL update from `src/Application/Usercp/Subpage/BuyZenSubpageController.php` into a repository/service.
- [ ] Extract the account-history query from `MyAccountSubpageController` into a dedicated query service.
- [ ] Split `src/Application/Rankings/RankingsService.php` into query builders / repositories / cache writer.

### Sprint 3 — transitional AdminCP cleanup

- [ ] Remove `selectedConfigFilePath` as a view contract.
- [ ] Split `downloads`, `vote`, `rankings`, `castlesiege`, and `email` into separate config handlers.
- [ ] Define the target model for former `views/admincp/mconfig/*.php`: partial view-models or mini-controllers.

### Sprint 4 — cron

- [ ] Introduce a `CronJobInterface`.
- [ ] Implement 1-2 pilot job classes (`server_info`, `temporal_bans`).
- [ ] Teach `CronExecutor` to resolve class-based jobs alongside legacy fallback.

## Definition of Done for an individual task

A task is considered complete when:

- [ ] the legacy pattern has been removed, not merely moved;
- [ ] public behavior has been preserved;
- [ ] tests have been added/updated;
- [ ] no new global constants / superglobal reads / dynamic includes have been introduced;
- [ ] documentation under `docs/` has been updated where needed.

## Anti-goals

For now, we **do not** do the following without a separate decision:

- [ ] a total rewrite of the entire project in a single PR;
- [ ] a move to a new framework just for the sake of moving;
- [ ] mass cosmetic refactoring without reducing the legacy surface;
- [ ] rewriting the installer as the first large chunk while `src/` is still leaking legacy patterns.

## Decision log

### 2026-04-01

- The project status has been recorded as **hybrid architecture**: the new controller/bootstrap/routing shell already exists, but legacy runtime/data/config patterns remain inside it.
- Priority number one is **not the old folders by themselves, but the old approaches still living inside `src/`**.


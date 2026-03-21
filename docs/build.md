# Frontend Assets

CSS and JS source files are served directly. There is no bundler, transpiler, or build CLI in this repository.

## CSS

The default theme receives a prepared layout context from
`Darkheim\Infrastructure\Theme\DefaultThemeLayoutBuilder`.
That builder prepares the final list of stylesheet URLs, and `public/themes/default/index.php`
only renders the resulting `<link>` tags.

See [CSS Architecture](css-architecture.md) for the full load order and naming conventions.

## JS

Each JS file is prepared in `DefaultThemeLayoutBuilder::build()` and rendered at the bottom of
`public/themes/default/index.php`.

| # | File | Purpose |
| :---: | :--- | :--- |
| 1 | jQuery 3.7.1 (CDN) | DOM manipulation |
| 2 | `main.js` | Server time clock, castle siege countdown, PayPal calculator |
| 3 | `events.js` | Event schedule feed (`/api/events.php`) |
| 4 | Bootstrap 3 JS (CDN) | Dropdowns, tooltips, modals |
| 5 | `public/assets/js/components.js` | DarkCore UI components (toasts, theme toggle, etc.) |

## Cache busting

Cache busting is automatic: `DefaultThemeLayoutBuilder` appends `filemtime()` as a query string to
every local asset URL. No manual version bumping is needed.

```text
css/style.css?v=1741819200
js/main.js?v=1741820000
```

## Adding a new CSS file

1. Create the file in `public/assets/css/` (for page/component styles) or `public/themes/default/css/` (for theme-level layout).
2. For `public/assets/css/`: add the filename (without `.css`) to the asset list in `DefaultThemeLayoutBuilder::stylesheetHrefs()`.
3. For `public/themes/default/css/`: register the new file in `DefaultThemeLayoutBuilder::stylesheetHrefs()` before `override.css`.

## Adding a new JS file

1. Create the file in `public/themes/default/js/` or `public/assets/js/`.
2. Register the final public URL in `DefaultThemeLayoutBuilder::build()`.
3. Let `public/themes/default/index.php` render the prepared script URL; do not add new runtime logic to the template.

## Changing the default theme shell

When you need to change the default layout (`navbar`, `header`, `sidebar`, `footer`, asset URLs):

1. Add or reshape the data in `Darkheim\Infrastructure\Theme\DefaultThemeLayoutBuilder`.
2. Render that prepared data in `public/themes/default/index.php` or `inc/modules/*.php`.
3. Keep request/config/cache/session reads out of the theme templates.


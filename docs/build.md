# Frontend Assets

CSS and JS source files are served directly. There is no bundler, transpiler, or build CLI in this repository.

## CSS

All stylesheets are injected with individual `<link>` tags directly in `templates/default/index.php`.  
No `main.css` entry point or `@import` bundling is used.

Assets from `assets/css/` are loaded dynamically:

```php
$_cssFiles = ['variables','toast','auth','ucp','myaccount','profiles',
              'info','tos','news','rankings','panels','paypal','downloads','castlesiege'];
foreach($_cssFiles as $_f) { /* inject <link> if file exists */ }
```

See [CSS Architecture](css-architecture.md) for the full load order and naming conventions.

## JS

Each JS file is included with its own `<script>` tag at the bottom of `<body>` in `templates/default/index.php`.

**Load order:**

| # | File | Purpose |
|---|------|---------|
| 1 | jQuery 3.7.1 (CDN) | DOM manipulation |
| 2 | `main.js` | Server time clock, castle siege countdown, PayPal calculator |
| 3 | `events.js` | Event schedule feed (`/api/events.php`) |
| 4 | Bootstrap 3 JS (CDN) | Dropdowns, tooltips, modals |
| 5 | `assets/js/components.js` | DarkWeb UI components (toasts, theme toggle, etc.) |

## Cache busting

Cache busting is automatic: `filemtime()` is appended as a query string to every local asset URL. No manual version bumping is needed.

```text
css/style.css?v=1741819200
js/main.js?v=1741820000
```

## Adding a new CSS file

1. Create the file in `assets/css/` (for page/component styles) or `templates/default/css/` (for template-level layout).
2. For `assets/css/`: add the filename (without `.css`) to the `$_cssFiles` array in `templates/default/index.php`.
3. For `templates/default/css/`: add a new `<link>` tag in `templates/default/index.php` **before** `override.css`.

## Adding a new JS file

1. Create the file in `templates/default/js/` or `assets/js/`.
2. Add a `<script>` tag at the bottom of `<body>` in `templates/default/index.php`.

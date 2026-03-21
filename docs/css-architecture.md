# CSS Architecture

This file documents how frontend styles are layered and where layout responsibilities live.

## Overview

| Location | Purpose |
| :--- | :--- |
| `public/themes/default/css/` | Template-level layout: structure, navbar, header, footer |
| `public/assets/css/` | Page/component styles — loaded by every theme |

Both sets are prepared by `Darkheim\Infrastructure\Theme\DefaultThemeLayoutBuilder` and rendered as individual `<link>` tags by `public/themes/default/index.php`. No bundler or `@import` entry point is used. Cache busting is automatic via `filemtime()` query strings.

## CSS load order

Order is critical — `override.css` must always be last.

| # | File | Location | Purpose |
| :---: | :--- | :--- | :--- |
| 1 | Bootstrap 3 CDN | CDN | Grid, components, utilities |
| 2 | Bootstrap Icons CDN | CDN | Icon font (`bi bi-*`) |
| 3 | `style.css` | `public/themes/default/css/` | Base theme: body, navbar, container, footer, hamburger menu |
| 4 | `profiles.css` | `public/themes/default/css/` | Old-style guild/player profile cards |
| 5 | `castle-siege.css` | `public/themes/default/css/` | Castle siege background image overrides |
| 6 | `variables.css` | `public/assets/css/` | CSS custom properties (`--dh-*`) |
| 7 | `toast.css` | `public/assets/css/` | Toast notification component |
| 8 | `auth.css` | `public/assets/css/` | Login / Register pages |
| 9 | `ucp.css` | `public/assets/css/` | User Control Panel |
| 10 | `myaccount.css` | `public/assets/css/` | My Account sub-page |
| 11 | `profiles.css` | `public/assets/css/` | Modern `pf-*` profile layout |
| 12 | `info.css` | `public/assets/css/` | Server info page |
| 13 | `tos.css` | `public/assets/css/` | Terms of Service page |
| 14 | `news.css` | `public/assets/css/` | News list and article pages |
| 15 | `rankings.css` | `public/assets/css/` | Rankings tables and class filter |
| 16 | `panels.css` | `public/assets/css/` | General panels and stat tables |
| 17 | `paypal.css` | `public/assets/css/` | Donation / PayPal page |
| 18 | `downloads.css` | `public/assets/css/` | Downloads page |
| 19 | `castlesiege.css` | `public/assets/css/` | Castle Siege page (`cs-*`) |
| 20 | **`override.css`** | `public/themes/default/css/` | **Last — wins all specificity** |

## CSS naming conventions

All DarkCore-owned utility classes use the `dh-` prefix.

### Global theme classes (`style.css`)

| Class | Purpose |
| :--- | :--- |
| `.dh-logo` | Server logo image (`<img>`) |
| `.dh-online-bar` | Online player progress bar container |
| `.dh-online-bar-progress` | Online bar filled portion |
| `.dh-lang-switcher` | Language switcher `<ul>` in the top bar |
| `.dh-powered` | "Powered by" footer link |

### CSS custom properties (`variables.css`)

| Variable | Light | Dark |
| :--- | :---: | :---: |
| `--dh-accent` | `#3f6588` | `#4a7aaa` |
| `--dh-gold` | `#c4a030` | `#d4aa50` |
| `--dh-text` | `#333333` | `#d7dde5` |
| `--dh-text-muted` | `#777777` | `#8a95a3` |
| `--dh-bg` | `#ffffff` | `#161d27` |
| `--dh-bg-card` | `#f1f1f1` | `#1a2230` |
| `--dh-border` | `#e3e3e3` | `#253140` |

### Info page class headers (`info.css`)

| Modifier | Character class |
| :--- | :--- |
| `.dk` | Dark Knight |
| `.dw` | Dark Wizard |
| `.elf` | Fairy Elf |
| `.mg` | Magic Gladiator |
| `.dl` | Dark Lord |
| `.sum` | Summoner |
| `.rf` | Rage Fighter |
| `.gl` | Grow Lancer |
| `.rw` | Rune Wizard |
| `.sl` | Slayer |

## Mobile responsive

All responsive behaviour is driven by `override.css`. No separate mobile stylesheet exists.

| Breakpoint | What changes |
| :---: | :--- |
| `≤ 991px` | Container becomes fluid, footer padding reduced, background `cover` |
| `≤ 768px` | Hamburger menu activates — `#navbar` gets solid background, `ul` hidden until `.active` toggled |
| `≤ 767px` | Grid columns stack (`col-xs-12`), tables get `overflow-x: auto`, rankings menu scrolls horizontally |
| `≤ 480px` | Logo max-width limited, font sizes reduced, footer columns stack |

The toggle button `#menu-toggle` is `display:none` on desktop and `display:inline-block` at `≤ 768px`.
JS in `index.php` toggles `.active` on `#navbar` to show/hide the `<ul>`. The icon switches between `bi-list` (☰) and `bi-x-lg` (✕).

## Dark mode

Dark mode is applied via the `html.dark-mode` class, set by `localStorage` on page load.
The toggle button `#theme-toggle` (`bi-moon-stars-fill` / `bi-sun-fill`) is in the top bar.
All dark mode overrides are in `override.css` under `html.dark-mode` selectors.

## The override system

`override.css` is loaded **last** and uses `!important` to guarantee precedence over base styles and Bootstrap 3.

- Component base styles → individual files in `assets/css/`
- Final visual values, layout corrections, mobile breakpoints → `override.css`

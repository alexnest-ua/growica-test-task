[English](04-architecture.en.md) · [Українська](04-architecture.uk.md)

# Архітектура та структура коду

## Структура репозиторію

```text
growica-test-task/
├── README.md / README.uk.md
├── .editorconfig / .gitignore
├── docs/                              # двомовні докси (01–06, *.en.md / *.uk.md)
└── themes/
    ├── verdal/                        # Дочірня тема A — редакційна / wellness
    └── meridian-edge/                 # Дочірня тема B — продукт / інженерія
```

Кожна дочірня тема має однакову форму (повний класичний набір шаблонів), тож вони
прямо порівнянні, але не мають спільного сліду:

```text
themes/<theme>/
├── style.css                # лише заголовок теми (унікальний для кожної)
├── functions.php            # setup, конвеєр ресурсів, хуки, SEO/clean-head, ACF, футер
├── inc/
│   └── template-tags.php     # багаторазові хелпери рендеру (мета, ACF-блок, опис для SEO)
├── template-parts/
│   ├── entry-header.php       # заголовок (+ мета запису) — для single і page
│   ├── content.php            # тіло одиночного запису
│   ├── content-card.php       # картка запису — для блогу / архіву / пошуку
│   └── content-none.php       # порожній стан — для блогу / архіву / пошуку
├── page.php  single.php  archive.php  search.php  404.php  index.php
├── css/
│   ├── main.css               # джерело
│   └── main.min.css           # збірка (підключається)
├── js/
│   ├── theme.js               # джерело
│   └── theme.min.js           # збірка (підключається)
├── fonts/                      # самохостингові woff2 (підмножина latin)
└── acf-json/                   # ACF Local JSON (авто-завантаження, відтворюваність)
```

Для встановлення теми збірка не потрібна — файли `*.min` закомічено. `csso` /
`terser` потрібні лише щоб перезібрати їх із джерел.

## Анатомія дочірньої теми

| Шлях | Призначення |
|------|-------------|
| `style.css` | Обов'язковий заголовок теми WordPress (унікальні Name/Author/Description/Version); стилі — у `css/`. |
| `functions.php` | Налаштування (i18n, меню футера, розмір зображення картки), enqueue, preload шрифтів, фільтри навігації/розкладки, точка завантаження ACF, SEO + clean-head, кастомний футер. |
| `inc/template-tags.php` | Багаторазові хелпери рендеру, що викликаються з шаблонів. |
| `template-parts/*` | Компоненти через `get_template_part()`, повторно використані в шаблонах. |
| `*.php` шаблони | Ієрархія шаблонів: page, single, archive, search, 404, index. |
| `css/`, `js/` | Джерела + мініфіковані ресурси. |
| `fonts/` | Самохостингові woff2. |
| `acf-json/` | Група полів ACF Local JSON. |

## Конвеєр ресурсів

Читабельні джерела (`css/main.css`, `js/theme.js`) мініфікуються у файли `*.min`
через `csso` і `terser`. `functions.php` підключає мініфіковані файли й вантажить
несатиснені джерела, коли увімкнено `SCRIPT_DEBUG`:

```php
$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
wp_enqueue_style( 'verdal-main', "{$uri}/css/main{$min}.css", array( 'generate-style' ), VERDAL_VERSION );
```

Стиль залежить від батьківського хендла **`generate-style`** — GeneratePress
підключає власний CSS під цим хендлом (з `assets/css/`, а не зі свого `style.css`),
тож дочірня тема вантажиться після нього, не підключаючи порожній батьківський
style.css.

## Самохостингові шрифти

Шрифти постачаються як woff2 (latin) у `fonts/`, оголошені через `@font-face` +
`font-display: swap` у `main.css`, а два «над згином» файли передзавантажуються
через фільтр `wp_preload_resources` — прибираючи стороннє з'єднання з Google Fonts
і захищаючи LCP/CLS.

## Шаблони та багаторазові частини

Шаблони володіють областю контенту; хедер і футер лишаються на хуках:

- Кожен шаблон викликає `get_header()` / `get_footer()`, тож masthead і кастомний
  футер досі надходять із хуків `functions.php` (без `header.php`/`footer.php`).
- Спільна розмітка живе в `template-parts/` і підключається через
  `get_template_part()` (напр. картка запису для блогу, архіву й пошуку).
- ACF рендериться **прямо в шаблонах** через `inc/template-tags.php`, тож не
  залежить від спрацювання контент-хука GeneratePress.
- `generate_sidebar_layout` примусово `no-sidebar`; кожна тема контролює міру
  контенту у CSS.

## Використані хуки й фільтри (перевизначати лише потрібне)

| Аспект | Механізм | Verdal (A) | Meridian Edge (B) |
|--------|----------|------------|-------------------|
| Позиція навігації | `generate_navigation_location` | `nav-below-header` (центровано) | `nav-float-right` (лого зліва/меню справа) |
| Розкладка | `generate_sidebar_layout` | `no-sidebar` | `no-sidebar` |
| Футер | зняти футер GP + `add_action('generate_footer', …)` (зняття відкладено до `after_setup_theme`) | 3 колонки + центрований копірайт | темний, 4 колонки + розділений нижній рядок |
| Preload шрифтів | `wp_preload_resources` | Lora 600 + Mulish 400 | Space Grotesk 600 + IBM Plex 400 |
| Джерело ACF | `acf/settings/load_json` → `acf-json/` | ✔ | ✔ |
| Рендер ACF | у шаблоні (`inc/template-tags.php`) | intro сторінки в `page.php` | CTA запису в `single.php` |
| Меню футера | `register_nav_menus('footer-menu')` | ✔ | ✔ |
| Очищення head | `init` / `wp_head` | прибрати generator/shortlink/RSD/WLW/emoji | той самий намір, власна реалізація |

## SEO та очищення head

Кожна тема видає власну легку метадату й прибирає відбитки WordPress із `<head>`.
**Метод відрізняється по темах**, і обидві поступаються спеціалізованому
SEO-плагіну (AIOSEO / Yoast / Rank Math), якщо такий активний:

- **Verdal** → meta description + Open Graph.
- **Meridian Edge** → Twitter Card + schema.org JSON-LD (Article / WebSite),
  закодований через `wp_json_encode( …, JSON_HEX_TAG | JSON_HEX_AMP )`, тож
  заголовок із `</script>` не може вирватися зі script-блоку.

## Прогресивно-покращувальний JS

Одне невелике покращення на vanilla-JS на тему, з урахуванням reduced-motion, із
повноцінною роботою без JS:

- **Verdal** — проявлення карток/intro «під згином» через `IntersectionObserver`
  (контент «над згином» не чіпається, щоб захистити LCP).
- **Meridian Edge** — ущільнення липкого хедера при прокручуванні (throttle через
  rAF, пасивний слухач; липкий CSS вмикається лише коли працює JS).

## Матриця розходження (без спільного сліду)

| Вісь | Verdal (A) | Meridian Edge (B) |
|------|------------|-------------------|
| Відчуття продукту | Редакційне / wellness | Продукт / інженерія |
| Фон / текст | `#f4f7f4` / `#18271f` (теплий зелений) | `#ffffff` / `#14161f` (холодний чорнильний) |
| Основний | `#1f6b53` бірюзово-зелений | `#2348c8` кобальт |
| Шрифт заголовків | Lora (serif) | Space Grotesk (grotesque) |
| Шрифт тіла | Mulish | IBM Plex Sans |
| Хедер | Центроване лого, навігація знизу | Лого зліва, навігація великими літерами справа |
| Футер | Світлий, 3 колонки, центрований курсив | Темний, 4 колонки, копірайт зліва + вгору |
| Копірайт | «© {y} {site}. Made calmly, by hand.» | «© {y} {site}. Built for speed.» |
| Метод SEO | meta description + Open Graph | Twitter Card + JSON-LD |
| JS | reveal-on-scroll | липкий хедер, що ущільнюється |
| Стиль карток | м'який, заокруглений, медіа зверху | різкий «spec-sheet», byline великими літерами |
| «Голос» коментарів | прозові банери | лаконічні маркери малими літерами |
| Текстовий домен / префікси | `verdal`, `--vd-*`, `.verdal-*`, `verdal_*` | `meridian-edge`, `--me-*`, `.me-*`, `meridian_edge_*` |
| Author / Version у `style.css` | Sagewright Studio / 1.1.0 | Brightseam Labs / 2.2.0 |
| ACF-група | Page Intro на сторінках, 5 полів | Post CTA Banner на записах, 6 полів + умовна логіка |

Теми не мають **жодних спільних авторських коментарів, докблоків, тіл хелперів,
стилю секційних коментарів, префіксів чи назв класів** — лише неминучу поверхню
API WordPress/CSS (напр. формат коментаря `translators:` за WPCS, `@font-face`,
ключі аргументів хуків).

## Безпека виводу та доступність

- Кожне динамічне значення екранується на виводі (`esc_html` / `esc_attr` /
  `esc_url` / `esc_html__` / `wp_kses_post`); масиви link/image з ACF
  перевіряються на null.
- Один `<h1>` на сторінку, логічний порядок заголовків, один `<main>`, орієнтири
  `<nav aria-label>`, семантика `<article>` / `<aside>` / `<footer>`.
- Контури focus-visible, цілі дотику ≥44px, палітри з контрастом AA, alt-тексти,
  обробка `prefers-reduced-motion` і утиліта `screen-reader-text` для контекстних
  посилань.

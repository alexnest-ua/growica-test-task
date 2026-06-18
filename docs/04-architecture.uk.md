[English](04-architecture.en.md) · [Українська](04-architecture.uk.md)

# Архітектура та структура коду

## Структура репозиторію

```text
growica-test-task/
├── README.md / README.uk.md
├── .editorconfig / .gitignore
├── bin/                               # відтворювана збірка ресурсів + вендоровані вхідні файли батька (не віддаються вебом)
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

Для встановлення теми збірка не потрібна — бандли `*.min` закомічено.
`bin/build-assets.sh` (через `csso` / `terser`) лише перезбирає їх із джерел.

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

## Конвеєр ресурсів та усунення сліду батька

Кожна дочірня тема віддається як **один самодостатній бандл на тип**, зібраний із
її читабельних джерел через `bin/build-assets.sh` (`csso` / `terser`):

- `css/main.min.css` = CSS-каркас GeneratePress + знімок динамічних пресетів сайту
  + компонент коментарів GP + власний CSS дочірньої теми;
- `js/theme.min.js` = батьківські скрипти меню + a11y (змінну конфігу меню
  перейменовано для кожного сайту) + покращення дочірньої теми.

Вендоровані батьківські вхідні файли лежать у `bin/vendor/`, **поза будь-якою
текою теми**, тож вони ніколи не віддаються вебом окремо. Оскільки каркас тепер
постачається всередині власного бандла дочірньої теми, `functions.php` підключає
лише цей бандл і прибирає кожне окреме посилання на батька зі сторінки:

```php
// підключаємо лише власний бандл дочірньої теми — без залежності generate-style
wp_enqueue_style( 'verdal-main', "{$uri}/css/main.min.css", array(), VERDAL_VERSION );
wp_enqueue_script( 'verdal-theme', "{$uri}/js/theme.min.js", array(), VERDAL_VERSION, true );

add_action( 'wp_enqueue_scripts', 'verdal_mask_parent_assets', 100 ); // знімає ресурси з теки батька + generate-child
add_filter( 'body_class', 'verdal_body_class' );                       // прибирає wp-theme-generatepress
add_filter( 'generate_print_a11y_script', '__return_false' );          // a11y GP натомість у бандлі
add_filter( 'wp_speculation_rules_configuration', '__return_null' );   // прибирає блок спекуляції з шляхом теми
```

Результат: відрендерений HTML не містить шляху `/themes/generatepress/`, хендла
`generate-*`, змінної `generatepressMenu` чи спільного `?ver=3.6.1`; єдині сліди
ресурсів кожного сайту (URL бандла, версія та інлайнова змінна конфігу меню)
унікальні. Повне обґрунтування й чесні залишки — у
[документі рішень](06-decisions.uk.md#усунення-сліду-батьківської-теми-generatepress-після-ревю).

### Полегшений `<head>`

Кожна тема також знімає найбільший стиль, який WordPress вбудовує інлайном — лист
пресетів `theme.json` (`wp_enqueue_global_styles`) — і прибирає базові стилі
`block-library` / `classic-theme`, яких не використовує, бо обидві теми малюють
винятково власними токенами й ніколи не торкаються блокових пресетів кольорів та
градієнтів. Далі WordPress вантажить лише невеликий стиль кожного *використаного*
блоку за потреби. Утиліта `screen-reader-text` живе в `main.css`, тож доступність
skip-link не залежить від знятих листів. Реалізовано різним кодом у кожній темі
(Verdal: явні виклики `wp_dequeue_style()`; Meridian Edge: цикл по масиву), тож це
не додає спільного сліду.

### Зображення

Усі контентні та Open Graph зображення віддаються у форматі **WebP**. Файли теми
`screenshot.png` та `apple-touch-icon.png` навмисно лишаються PNG — WordPress
вимагає PNG/JPG для скриншота теми, а іконки домашнього екрана iOS мають бути PNG.
Featured-зображення оголошують `width`/`height` (без CLS); зображення-герой LCP
використовує `fetchpriority="high"`, а медіа «під згином» — `loading="lazy"`.

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
- **Герой головної сторінки** рендериться над `#content` через хук
  `generate_after_header`. GeneratePress робить `#content` flex-рядком, тож герой,
  виведений зсередини шаблону, став би *поруч* із колонкою контенту; хук розміщує
  його на повну ширину зверху. Головний цикл проганяється один раз, а
  `rewind_posts()` відновлює його для власного циклу шаблону, лишаючи рівно один
  `<h1>` на сторінку.
- Інший вивід ACF (внутрішній блок page-intro у Verdal, CTA запису в Meridian)
  рендериться **прямо в шаблонах** через `inc/template-tags.php`, тож не залежить
  від спрацювання контент-хука GeneratePress.
- `generate_sidebar_layout` примусово `no-sidebar`; кожна тема контролює міру
  контенту у CSS.

## Використані хуки й фільтри (перевизначати лише потрібне)

| Аспект | Механізм | Verdal (A) | Meridian Edge (B) |
|--------|----------|------------|-------------------|
| Позиція навігації | `generate_navigation_location` | `nav-below-header` (центровано) | `nav-float-right` (лого зліва/меню справа) |
| Розкладка | `generate_sidebar_layout` | `no-sidebar` | `no-sidebar` |
| Герой головної | `generate_after_header` (на повну ширину, над `#content`) | eyebrow + заголовок + лід + зображення | пігулка + заголовок + лід + пара CTA |
| Прибирання CSS ядра | зняти `wp_enqueue_global_styles`; dequeue `block-library`/`classic-theme` | ✔ явні виклики | ✔ цикл по масиву |
| Футер | зняти футер GP + `add_action('generate_footer', …)` (зняття відкладено до `after_setup_theme`) | 3 колонки + центрований копірайт | темний, 4 колонки + розділений нижній рядок |
| Preload шрифтів | `wp_preload_resources` | Lora 700 + Mulish 400 | Space Grotesk 700 + IBM Plex 400 |
| Джерело ACF | `acf/settings/load_json` → `acf-json/` | ✔ | ✔ |
| Рендер ACF | герой через `generate_after_header`; решта в шаблоні | Page Intro → герой головної | CTA запису в `single.php` |
| Меню футера | `register_nav_menus('footer-menu')` | ✔ | ✔ |
| Очищення head | `init` / `wp_head` | прибрати generator/shortlink/RSD/WLW/emoji | той самий намір, власна реалізація |
| Маскування ресурсів батька | `wp_enqueue_scripts` (пріор. 100): dequeue за текою джерела + `generate-child` | ✔ | ✔ |
| Класи body | `body_class`: прибрати `wp-theme-generatepress` / `wp-child-theme-*` | ✔ | ✔ |
| a11y батька | `generate_print_a11y_script` → `false` (поведінка у бандлі) | ✔ | ✔ |
| Правила спекуляції | `wp_speculation_rules_configuration` → `null` (прибирає блок зі шляхом теми) | ✔ | ✔ |

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
| Author / Version у `style.css` | Sagewright Studio / 1.3.0 | Brightseam Labs / 2.4.0 |
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

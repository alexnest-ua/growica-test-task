[English](05-database.en.md) · [Українська](05-database.uk.md)

# База даних та структура даних

Теми **не додають кастомних таблиць** і не виконують міграцій. Вони читають і
пишуть лише стандартні таблиці ядра WordPress. Цей документ фіксує, яких саме
даних торкається кожна тема і як ACF їх зберігає.

## Задіяні таблиці (усі — ядро WordPress)

| Таблиця | Для чого |
|---------|----------|
| `wp_options` | Налаштування головної сторінки, потеми `theme_mods_*` (вкл. `nav_menu_locations`), власні налаштування ACF. |
| `wp_postmeta` | **Значення полів ACF** (і посилання на ключі полів ACF), для кожного запису/сторінки. |
| `wp_posts` | Демо-сторінки/записи та записи `nav_menu_item` для меню. |
| `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships` | Меню (таксономія `nav_menu`) та їхні елементи. |

## ACF: визначення проти значень (розподіл відтворюваності)

- **Визначення групи полів** живе в **репозиторії** як ACF Local JSON
  (`themes/<theme>/acf-json/*.json`), авто-завантажується через фільтр
  `acf/settings/load_json`. Це частина, відтворювана з репозиторію, — вона *не*
  лише «кліками» в базі даних.
- **Значення полів** (те, що редактор вводить на сторінці/записі) живуть у
  `wp_postmeta`.

ACF зберігає кожне поле як **два** рядки meta: значення плюс рядок із префіксом
`_`, що містить **ключ** поля (щоб ACF міг розв'язати визначення поля).

### Verdal — «Page Intro» на головній (запис 5774)

```text
intro_eyebrow    A quiet practice
_intro_eyebrow   field_verdal_intro_eyebrow
intro_lead       Verdal is a calm, unhurried space for slow li…
_intro_lead      field_verdal_intro_lead
intro_cta        a:3:{s:5:"title";s:14:"Book a session";…}   (серіалізований масив link)
_intro_cta       field_verdal_intro_cta
intro_boxed      1
_intro_boxed     field_verdal_intro_boxed
```

### Meridian Edge — «Post CTA Banner» на записі (запис 5780)

```text
cta_enabled      1
_cta_enabled     field_me_cta_enabled
cta_kicker       Try it free
cta_heading      Ship your next idea on the edge
cta_text         Spin up a globally distributed preview e…
cta_button       a:3:{s:5:"title";s:14:"Start building";…}   (серіалізований масив link)
cta_variant      solid
_cta_variant     field_me_cta_variant
```

Дві групи **структурно різні** (5 проти 6 полів, інші назви/типи полів, інше
правило розташування — `page` проти `post`) і відмінні від батьківської
(GeneratePress не постачає ACF-групи).

## Розташування меню — потемно

Кожна тема реєструє розташування `footer-menu` (розташування `primary` походить
від GeneratePress). *Призначення* меню до розташування — це **theme mod**, тож
воно зберігається для кожної таблиці стилів окремо:

```text
wp_options → theme_mods_verdal         (nav_menu_locations: primary, footer-menu)
wp_options → theme_mods_meridian-edge  (nav_menu_locations: primary, footer-menu)
```

Саме тому перемикання активної теми вимагає повторного призначення меню —
розташування незалежні для кожної теми. (При розгортанні кожен сайт призначає
свої власні.)

## Налаштування головної сторінки (демо-контент)

```text
wp_options → show_on_front  = page
wp_options → page_on_front  = <id сторінки Home>   # задіює intro сторінки Verdal
wp_options → page_for_posts = <id сторінки Journal>
```

## Локальна база даних розробки

| Факт | Значення |
|------|----------|
| Назва БД | `test_wp` |
| Хост | `localhost` (MySQL 8.0.46) |
| Префікс таблиць | `wp_` |

Облікові дані живуть лише в `/var/www/testwp/wp-config.php` на dev-машині й
**ніколи** не комітяться (`wp-config.php` у git-ignore). Жоден файл тем не
зашиває облікові дані, хост чи секрет.

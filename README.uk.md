<!-- Мова: Українська | [English](README.md) -->

# Тестове завдання Growica — унікалізація теми

[English](README.md) · [Українська](README.uk.md)

Дві **дочірні теми** GeneratePress, зроблені так, щоб відчуватися як **різні
продукти** — практична відповідь на «унікалізацію теми / усунення сліду»:
зробити сайти, що мають спільну батьківську тему, такими, що виглядають і
читаються як незалежні проєкти, аби вони не кластеризувалися як одна мережа.

- **Verdal** — редакційна / wellness (теплий зелений, Lora + Mulish, центрований masthead, світлий 3-колонковий футер).
- **Meridian Edge** — продукт / інженерія (кобальт + чорнило, Space Grotesk + IBM Plex Sans, хедер лого-зліва/меню-справа, темний 4-колонковий футер).

Між ними **немає спільного сліду**: незалежні заголовки `style.css`, кольори,
шрифти, структура хедера/футера, текстові домени, префікси класів та ACF-групи.

## Живі демо

> Розгорнуто на InstaWP, з увімкненим *Settings → Reading → Discourage search
> engines* на кожному (згідно з вимогою noindex). Безкоштовні хостовані сайти
> тимчасові — якщо посилання застаріло, теми встановлюються з цього репозиторію
> за лічені хвилини (див. [Встановлення](#встановлення-локально)).

| Тема | Жива адреса |
|------|-------------|
| **Verdal** | _додається після розгортання_ |
| **Meridian Edge** | _додається після розгортання_ |

## Чому GeneratePress

Найлегша з GeneratePress / Astra / Kadence, з архітектурою хуків/фільтрів, що
дозволяє двом дочірнім темам розходитися **структурно в коді** (позиція
навігації, хедер, футер) майже без дублювання шаблонів — найчистіший спосіб
продемонструвати усунення сліду. Повне обґрунтування, зокрема компроміс спільної
батьківської теми, — у [docs/06-decisions](docs/06-decisions.uk.md).

## Структура репозиторію

```text
themes/verdal/           Дочірня тема A  (style.css · functions.php · acf-json/)
themes/meridian-edge/    Дочірня тема B  (style.css · functions.php · acf-json/)
docs/                    Двомовна документація (індекс нижче)
```

Глибший розбір — анатомія дочірньої теми, використані хуки й повна матриця
розходження A проти B — у [docs/04-architecture](docs/04-architecture.uk.md).

## Встановлення локально

Вимоги: WordPress 6.5+, PHP 7.4+ (зібрано й перевірено на PHP 8.3 / WP 7.0).

1. **Батьківська тема** — встановити *GeneratePress*:
   `Appearance → Themes → Add New → пошук "GeneratePress" → Install`.
2. **ACF (free)** — `Plugins → Add New → пошук "Advanced Custom Fields" →
   Install → Activate`.
3. **Дочірня тема** — скопіювати **одну** теку в `wp-content/themes/`:
   ```bash
   cp -r themes/verdal /path/to/wp-content/themes/
   # або
   cp -r themes/meridian-edge /path/to/wp-content/themes/
   ```
4. **Активувати** дочірню тему (`Appearance → Themes`).
5. **Меню** — `Appearance → Menus`: призначити меню до **Primary** і до
   **Footer Menu**.
6. **ACF уже відтворюваний** — група полів авто-завантажується з теки
   `acf-json/` теми (імпорт не потрібен). Щоб побачити в дії, відредагуйте
   **сторінку** (Verdal → «Page Intro») або **запис** (Meridian Edge → «Post CTA
   Banner»), заповніть поля й перегляньте фронтенд.

> Дві дочірні теми встановлюються так само на хостованих демо. Кожна — класична
> дочірня тема, без кроку збірки.

## Документація

| Тема | English | Українська |
|------|---------|------------|
| Вимоги | [01-requirements.en](docs/01-requirements.en.md) | [01-requirements.uk](docs/01-requirements.uk.md) |
| Частина 1 — Рішення (усунення сліду) | [02-solution.en](docs/02-solution.en.md) | [02-solution.uk](docs/02-solution.uk.md) |
| План реалізації | [03-implementation-plan.en](docs/03-implementation-plan.en.md) | [03-implementation-plan.uk](docs/03-implementation-plan.uk.md) |
| Архітектура та код | [04-architecture.en](docs/04-architecture.en.md) | [04-architecture.uk](docs/04-architecture.uk.md) |
| База даних та дані | [05-database.en](docs/05-database.en.md) | [05-database.uk](docs/05-database.uk.md) |
| Рішення та компроміси | [06-decisions.en](docs/06-decisions.en.md) | [06-decisions.uk](docs/06-decisions.uk.md) |

## Технології та версії

PHP 8.3 · MySQL 8.0 · WordPress 7.0 · GeneratePress 3.6.1 · ACF (free) 6.8.4.

## Нотатки

- `noindex` — це **налаштування сайту** на кожному демо (не зашите в темі):
  індексація це питання розгортання.
- Приватний бриф завдання свідомо **не** комітиться в цей публічний репозиторій.
- Ідентичність git для цього репозиторію встановлено локально на `alexnest-ua`.

# dm-sync — синхронизация `.dm-page` между worktree / local / remote

## Роли

| Контур | Путь | Назначение |
|---|---|---|
| **Worktree (git)** | этот репозиторий | источник HTML/CSS/images |
| **Local** | `https://localhost:8443/` (`/Users/viktorgromov/itweb-new`) | вёрстка и проверка |
| **Remote** | `https://itweb-new.acrobat.test-itweb.ru/` | приёмка; «задеплой» |

MCP `itweb-ai` не использовать.

## Команды

Из корня worktree:

```bash
# догнать local всем манифестом + CSS
scripts/dm-sync-page.sh --env local --all --css

# одна страница → local
scripts/dm-sync-page.sh --env local --code promo-sayt

# одна страница → remote (после проверки на local)
scripts/dm-sync-page.sh --env remote --code promo-sayt --css

# оба контура
scripts/dm-sync-page.sh --env both --code promo-sayt --css
```

## Файлы

- `dm-pages.manifest.json` — CODE ↔ HTML ↔ section ↔ meta
- `dm-sync-upsert.php` — upsert элемента/раздела через mysqli (без Bitrix prolog)
- `dm-sync-page.sh` — копирование файлов + вызов upsert + clear cache

Новую страницу: добавить HTML в `design-model/pages/`, запись в манифест, затем `--env local --code …`.

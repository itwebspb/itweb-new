# Git-воркфлоу для itweb-new (1C-Bitrix)

## Окружения

| Окружение | URL | Назначение |
|-----------|-----|------------|
| Тестовый сервер | https://itweb-new.acrobat.test-itweb.ru/ | Общая dev/stage-копия, полная установка Bitrix |
| Локально / Cloud Agent | http://itweb-new.local/ | Разработка в репозитории |
| GitHub | https://github.com/itwebspb/itweb-new | Единый источник кода |

## Что хранится в Git

В репозитории — **только ваш код**, не вся установка Bitrix:

- публичные страницы (`/catalog`, `/company`, …);
- кастомные модули Aspro, ESOL и др. (`bitrix/modules/aspro.*`, `esol.*`);
- кастомные компоненты и шаблоны (`bitrix/components/aspro/*`, `bitrix/templates/*`);
- `include/`, меню, ajax-обработчики;
- конфигурация dev-окружения (`.cursor/`).

**Не коммитим** (см. `.gitignore`):

- ядро Bitrix (`bitrix/header.php`, `bitrix/modules/main`, …);
- `upload/`, кеш, логи;
- секреты: `.settings.php`, `dbconn.php`;
- сгенерированные sitemap, фиды, бэкапы.

После установки новых partner-модулей пересоберите `.gitignore` (если есть `generate-gitignore.php` на сервере).

## Ветки

```
master          ← продакшен / тестовый сервер (деплой сюда)
  └── cursor/*  ← задачи Cloud Agent
  └── feature/* ← ручная разработка (по желанию)
```

Правила:

1. **`master`** — всегда рабочий код, готовый к выкладке на тестовый сервер.
2. Каждая задача — отдельная ветка от `master`.
3. Слияние через **Pull Request** на GitHub (ревью + история).
4. На сервере только `git pull`, без прямых правок файлов.

Именование веток Cloud Agent: `cursor/<краткое-описание>-7bec`.

## Ежедневный цикл разработки

```bash
# 1. Актуализировать master
git checkout master
git pull origin master

# 2. Создать ветку задачи
git checkout -b feature/my-task

# 3. Работа, коммиты
git add ...
git commit -m "Краткое описание изменения"

# 4. Пуш и PR
git push -u origin feature/my-task
# → открыть Pull Request в master на GitHub
```

Рекомендуемые настройки Git (один раз на машине):

```bash
bash scripts/setup-git.sh
```

## SSH к тестовому серверу

Сервер: `itweb-new@itweb-new.acrobat.test-itweb.ru` (доступ по ключу).

```bash
# Положить приватный ключ в переменную окружения
export ITWEB_NEW_SSH_KEY="$(cat /path/to/private_key)"
bash scripts/setup-ssh.sh

# Проверка
ssh itweb-new-test "hostname && pwd"
```

Для **Cursor Cloud Agent** добавьте секрет `ITWEB_NEW_SSH_KEY` в настройках Environment, затем в `install` или вручную выполните `bash scripts/setup-ssh.sh`.

## Локальный сайт (без полного ядра в git)

Ядро Bitrix на диск не попадает — для работы `http://itweb-new.local/` нужно один раз подтянуть файлы с сервера:

```bash
bash scripts/setup-ssh.sh          # если ещё не настроен SSH
bash scripts/sync-bitrix-core.sh   # rsync ядра с тестового сервера
```

Отдельно импортируйте дамп БД с тестового сервера в локальный MariaDB (`sitemanager`, пользователь `bitrix`).

## Деплой на тестовый сервер

После merge в `master`:

```bash
ssh itweb-new-test
cd /var/www/itweb-new/data/www/itweb-new.acrobat.test-itweb.ru   # уточните путь на сервере
bash scripts/deploy-on-server.sh
```

Скрипт делает `git pull --ff-only` и очищает кеш Bitrix.

## Коммиты

- Сообщения на русском или английском — единообразно в рамках задачи.
- Один коммит = одна логическая правка.
- Перед push: `git status` — убедитесь, что нет `.settings.php`, `upload/`, кеша.

## Проверка доступов (чеклист)

| Ресурс | Команда / действие | Ожидание |
|--------|-------------------|----------|
| GitHub read | `git fetch origin` | без ошибок |
| GitHub write | `git push` из ветки | без 403 |
| Сайт HTTP | `curl -I https://itweb-new.acrobat.test-itweb.ru/` | `200 OK` |
| SSH | `ssh itweb-new-test hostname` | имя хоста |
| Локальный Apache | `curl -I http://itweb-new.local/` | `200` после sync ядра + БД |

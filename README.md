# altered.wiki — Docker setup

MediaWiki 1.44 stack for [altered.wiki](https://altered.wiki), a community encyclopedia for psychoactive substances.

## Stack

| Service | Image |
|---|---|
| MediaWiki | `mediawiki:1.44` (custom build) |
| Database | `mariadb:lts` |
| Search | `opensearchproject/opensearch:1.3.14` |
| Cache | `redis:7-alpine` |

## First run

```sh
cp .env.example .env
```

Fill in `.env`:

| Variable | Description |
|---|---|
| `DB_PASSWORD` | MariaDB password for the `mediawiki` user |
| `DB_ROOT_PASSWORD` | MariaDB root password |
| `MW_SECRET_KEY` | MediaWiki secret key — `openssl rand -hex 32` |
| `MW_UPGRADE_KEY` | MediaWiki upgrade key — `openssl rand -hex 32` (optional) |
| `MW_ADMIN_PASSWORD` | Password for the `Admin` wiki account |
| `MW_SERVER` | Public URL of the wiki, e.g. `https://altered.wiki` |
| `ALTCHA_HMAC_KEY` | HMAC key for the Altcha CAPTCHA — `openssl rand -hex 32` |
| `SMTP_HOST` | SMTP server hostname |
| `SMTP_PORT` | SMTP port (default: `587`) |
| `SMTP_USER` | SMTP login username |
| `SMTP_PASSWORD` | SMTP login password |
| `SMTP_FROM` | From address for outgoing wiki mail |

Then start:

```sh
docker compose up -d
```

On first start the entrypoint automatically runs the MediaWiki installer, applies database migrations, initializes Semantic MediaWiki, sets up the CirrusSearch index, and writes initial system messages. Subsequent restarts only run `update.php`.

## Configuration

- `config/LocalSettings.php` — bind-mounted into the container; edit and restart to apply changes (OPcache caches PHP, so a restart is required).
- `entrypoint.sh` — bind-mounted; edits take effect on next restart without a rebuild.

## Extensions

**Via Composer:** SemanticMediaWiki, SemanticResultFormats, PageForms, Maps, DynamicPageList4

**Via git clone:** Elastica, CirrusSearch, PDFEmbed, SemanticDrilldown, Citizen (skin), [AltchaCaptcha](https://github.com/Altered-Wiki/AltchaCaptcha)

**Bundled with MediaWiki:** ParserFunctions, WikiEditor, CodeEditor, TemplateData, Scribunto, VisualEditor, Echo, Thanks, AbuseFilter, TitleBlacklist, ConfirmEdit

## Custom namespaces

| Namespace | ID |
|---|---|
| Substance / Substance_talk | 3000 / 3001 |
| Research / Research_talk | 3002 / 3003 |
| Experience / Experience_talk | 3004 / 3005 |

## Permissions

Public read. Account required to edit. Account creation open.

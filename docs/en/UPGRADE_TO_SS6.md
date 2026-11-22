# Upgrading `sunnysideup/uuid` to Silverstripe CMS 6

## Requirements

### ⚠️ BREAKING CHANGE: Silverstripe 4 and 5 are no longer supported

`silverstripe/framework` is now constrained to `^6.0` (previously `^4.0 || ^5.0`).

- Upgrade your project to Silverstripe CMS 6 before updating this module.
- Stay on the previous release of this module if you are still on SS4 or SS5.

### ⚠️ BREAKING CHANGE: PHP 8.3+ required

`HashCreator::CHARS` is now a typed constant (`private const string`), which needs PHP 8.3 or later. Silverstripe 6 already requires a compatible PHP version, so this only matters if you pin PHP separately.

## Namespace

### ⚠️ BREAKING CHANGE: `Sunnysideup\UUDI` renamed to `Sunnysideup\UUID`

The old namespace had a typo (`UUDI`) and did not match the PSR-4 mapping in `composer.json` (`Sunnysideup\UUID\` => `src/`). As a result, the classes could not be autoloaded. All classes now use the correct namespace.

Search your project for `Sunnysideup\UUDI` and replace it with `Sunnysideup\UUID`. Check these places:

| Old | New |
| --- | --- |
| `Sunnysideup\UUDI\Api\HashCreator` | `Sunnysideup\UUID\Api\HashCreator` |
| `Sunnysideup\UUDI\Extensions\UUIDExtension` | `Sunnysideup\UUID\Extensions\UUIDExtension` |

- `use` statements in PHP code.
- YAML config that applies the extension, for example `extensions:` entries.
- YAML config that sets `HashCreator` options, such as `always_require_uuid`.

**🚨 CRITICAL REVIEW REQUIRED: YAML config that still uses the old `UUDI` namespace will fail silently or throw "class not found" errors. Search all YAML files in `app/_config` and any other modules that reference this extension.**

The commented example in `_config/config.yml` was corrected in the same way:

```yaml
Sunnysideup\UUID\Api\HashCreator:
  always_require_uuid: true
```

## Behaviour fixes

### `RequiresUUID` checkbox is now actually read-only

In `UUIDExtension`, `->performDisabledTransformation` was missing its `()`, so the "Hash Public UUID" checkbox was never made read-only. It is now called as a method.

- The checkbox now displays as disabled in the CMS. Editors can no longer toggle it there.
- If you relied on editing this field through the CMS, set `RequiresUUID` in code or through your own form field instead.

## Housekeeping

- `composer.json` was reformatted to 4-space indentation. This has no functional effect.
- `.agents/` was added to `.gitignore`.

## Outstanding items

**🚨 CRITICAL REVIEW REQUIRED: `.travis.yml` is still the old CI config (old PHP version and distribution). It is not used at runtime but is outdated. Update it or remove it.**

**🚨 RISKY: `HashCreator::create_hash_id()` builds its hash with `md5(class:id)`, which is guessable. The upgrade did not change this. The random suffix added in `UUIDExtension::getHashID()` mitigates it. Do not use `create_hash_id()` on its own for anything security-sensitive.**

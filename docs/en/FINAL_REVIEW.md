# Final review

## Fixed
- `src/Api/HashCreator.php`, `src/Extensions/UUIDExtension.php`, `_config/config.yml`: namespace `Sunnysideup\UUDI` (typo) changed to `Sunnysideup\UUID` so it matches the PSR-4 autoload in `composer.json` (classes could not be autoloaded).
- `src/Extensions/UUIDExtension.php`: `->performDisabledTransformation` was missing `()`, so the checkbox was never transformed. It is now called as a method.

## ⚠️ Upgrade work still to do
- `.travis.yml`: outdated CI config (old PHP/dist); not relevant to runtime but should be updated or removed.
- `src/Api/HashCreator.php`: `create_hash_id()` uses `md5(class:id)`, which is guessable. The random suffix added in `UUIDExtension::getHashID()` mitigates this, so no change was made.

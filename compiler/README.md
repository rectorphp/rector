# PHAR Compiler for Rector

## Compile the PHAR

```bash
composer install
php bin/compile [version] [repository]
```

Default `version` is `master`, and default `repository` is `https://github.com/rector/rector.git`.

The compiled PHAR will be in `tmp/rector.phar`. Test it:

```bash
php ../tmp/rector.phar
```

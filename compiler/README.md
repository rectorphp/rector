# PHAR Compiler for Rector

## Compile the PHAR

```bash
composer update
php bin/compile
```

The compiled PHAR will be in `tmp/rector.phar`. Test it:

```bash
php ../tmp/rector.phar
```

Please note that running the compiler will change the contents of `composer.json` file and `vendor` directory. Revert those changes after running it.

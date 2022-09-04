# How to Persist Cache Between CI Runs
While parsing your application code, Rector generates objects representing that code. It caches these objects for later reuse, so it doesn't have to parse the entire application again, by detecting which files have changed since the last Rector run.

When running Rector in a Continuous Integration (CI) system such as [GitHub Actions](https://github.com/features/actions), the default implementation [uses an in-memory cache](https://github.com/rectorphp/rector/blob/1d28ca109ca536e8034c3c756ee61c65e6e63c8a/config/config.php#L89-L94). This means the next job that runs, will have to parse all code from scratch.

```php
use Rector\Caching\ValueObject\Storage\FileCacheStorage;

return static function (RectorConfig $rectorConfig): void {
    // ... your config

    // Ensure file system caching is used instead of in-memory.
    $rectorConfig->cacheClass(FileCacheStorage::class);

    // Specify a path that works locally as well as on CI job runners.
    $rectorConfig->cacheDirectory('./var/cache/rector');
};
```

Note that this caches relative to the repository directory, so it's the same directory on build as on development (and other environments). The actual path, when not specified, may vary per (runner) OS.

## Debugging the Cache Locally
Generate the cache on your development machine, by running the command:
```bash
vendor/bin/rector process --dry-run --config=rector.php
```
You can find it in your repository directory under `./var/cache/rector/`, containing folders like `0a`, `0b`, `0c`, ... containing the cache objects representing the latest run.

This, preferably prepended with `php `, command is also what your CI action should run, after mapping the cache directory from an earlier run.

## GitHub Actions
On GitHub Actions, you can use the [built-in cache action](https://github.com/actions/cache) as a step to point to a path that you want cached between jobs:

```yaml
      - name: Rector Cache
        uses: actions/cache@v3
        with:
          path: ./var/cache/rector
          key: ${{ runner.os }}-rector-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-rector-

      - name: Rector Dry Run
        run: php vendor/bin/rector process --dry-run --config=rector.php
```
In this key configuration, runs on branches inherit the cache from their parent, if any.

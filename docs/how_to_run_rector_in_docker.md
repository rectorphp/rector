# How to Run Rector in Docker

You can run Rector on your project using Docker.
To make sure you are running latest version, use `docker pull rector/rector`.

*Note that Rector inside Docker expects your application in `/project` directory - it is mounted via volume from the current directory (`$pwd`).*

```bash
docker run --rm -v $(pwd):/project rector/rector:latest process src --dry-run
```

Using `rector.php` config:

```bash
docker run --rm -v $(pwd):/project rector/rector:latest process src \
    --config rector.php \
    --dry-run
```

## Multiple PHP versions supported

You should always use image with PHP version closest to your project's.

Rector Docker images supports PHP versions 7.3, 7.4, 8.0.

```
## Using with specific tagged version of Rector
rector/rector:0.9.32-php7.3
rector/rector:0.9.32-php7.4
rector/rector:0.9.32-php8.0

## Using latest release
rector/rector:php7.3    # same as rector/rector:latest-php7.3
rector/rector:php7.4    # same as rector/rector:latest-php7.4
rector/rector:php8.0    # same as rector/rector:latest-php8.0
```

## Permissions issues

If you run into issues with `permission denied` or running Rector in docker keeps changing owner of your project files, running container as current user `--user $(id -u):$(id -g)` should solve it for you:
```
docker run --rm --user $(id -u):$(id -g) -v $(pwd):/project rector/rector process src --config rector.php --dry-run
```

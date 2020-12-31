# How to Run Rector in Docker

You can run Rector on your project using Docker.
To make sure you are running latest version, use `docker pull rector/rector`.

*Note that Rector inside Docker expects your application in `/project` directory - it is mounted via volume from the current directory (`$pwd`).*

```bash
docker run --rm -v $(pwd):/project -w /rector rector/rector:latest process /project/src --dry-run
```

Using `rector.php` config:

```bash
docker run --rm -v $(pwd):/project -w /rector rector/rector:latest process /project/src \
    --autoload-file=/project/vendor/autoload.php \
    --config /project/rector.php \
    --dry-run
```

## Permissions issues

If you run into issues with `permission denied` or running Rector in docker keeps changing owner of your project files, running container as current user `--user $(id -u):$(id -g)` should solve it for you:
```
docker run --rm --user $(id -u):$(id -g) -v $(pwd):/project -w /rector rector/rector process /project/src --config /project/rector.php --dry-run
```

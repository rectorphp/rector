# How to Run Rector in Docker

You can run Rector on your project using Docker.
To make sure you are running latest version, use `docker pull rector/rector`.

*Note that Rector inside Docker expects your application in `/project` directory - it is mounted via volume from the current directory (`$pwd`).*

```bash
docker run --rm -v $(pwd):/project rector/rector:latest process src --set symfony40 --dry-run
```

Using `rector.php` config:

```bash
docker run --rm -v $(pwd):/project rector/rector:latest process src \
    --config rector.php \
    --dry-run
```

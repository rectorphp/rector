## Docker image for Xdebug debugging

### Build

Builds image with `rector-xdebug` tag.

```shell
docker build . --tag rector-xdebug --file .docker/php-xdebug/Dockerfile
```

You can use `--build-arg PHP_VERSION=7.4` to build with specific PHP version. Supported versions are: 7.3, 7.4, 8.0

### Usage

Get into container (change ip address):

```shell
docker run -it --rm \
  --entrypoint="" \
  --volume $(pwd):/rector \
  --env XDEBUG_CONFIG="client_host=172.16.165.1" \
  --env PHP_IDE_CONFIG="serverName=rector" \
  rector-xdebug bash
```

**Do not forget to run rector binary with `--xdebug` option.**

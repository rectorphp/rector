## Docker image for Xdebug debugging

### Build

Builds image with `rector-xdebug` tag.

```
docker build . --tag rector-xdebug --file .docker/php-xdebug/Dockerfile
```


### Usage

Get into container (change ip address):

```shell
docker run -it --rm \
  --entrypoint="" \
  --volume $(pwd):/rector \
  --env XDEBUG_CONFIG="client_host=172.16.165.1" \
  --env PHP_IDE_CONFIG="serverName=getrector_org" \
  rector-xdebug bash
```

**Do not forget to run rector binary with `--xdebug` option.**

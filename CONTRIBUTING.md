# How to Contribute

Contributions here are more than welcomed! You can contribute to [rector-src](https://github.com/rectorphp/rector-src) repository or one of [extension packages](https://github.com/rectorphp/).

## Preparing Local Environment

1. Fork the [rector/rector-src](https://github.com/rectorphp/rector-src) repository and clone it

```bash
git clone git@github.com:<your-name>/rector-src.git
cd rector-src
```

2. We use PHP 8.2 and composer

Install dependencies and verify your local environment:

```bash
composer update
composer check-platform-reqs
```

*Note: using Docker for contributing is strongly discouraged, as it requires [extra knowledge of composer internals](https://github.com/composer/composer/issues/9368#issuecomment-718112361).*

Then you can start working with the code :+1:

<br>

Do you want to **contribute a failing test**? [This tutorial will show you how](https://getrector.com/documentation/reporting-issue-with-rector)

## Preparing Pull Request

3 steps will make your pull-request easy to merge:

- **1 feature per pull-request**
- **new features need tests**
- CI must pass... you can mimic it locally by running

    ```bash
    composer complete-check
    ```

- Do you need to fix coding standards?

    ```bash
    composer fix-cs
    ```

We would be happy to accept PRs that follow these guidelines.

### Using Docker

A `docker-compose.yml` file is provided to make it easier to run the CI checks locally.
To use it, you need to have Docker installed on your machine, then you can build the image and execute the
above commands in a docker container:

```bash
# Build the docker image
docker compose build

# Run the entire CI suite
docker compose run php composer complete-check

# Fix the coding standards
docker compose run php composer fix-cs
```

## TroubleShooting
If you are on macOS, and got hang on applying patch, you may need to install gpatch, you can install with:

`brew install gpatch`

## Repository layout
Documentation goes into `build/target-repository/docs`.

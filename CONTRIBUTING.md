# How to Contribute

Contributions here are more than welcomed! You can contribute to [rector-src](https://github.com/rectorphp/rector-src) repository.

## Preparing Local Environment

If you have PHP 8 and Composer installed locally you can use it straight away. You can validate your environment with:

```bash
composer check-platform-reqs
```

Alternatively you can use Docker runtime. All you need to do is wrap every command with `docker-compose run php`, so commands will be executed inside Docker container.

For example, to download PHP dependencies:

```bash
docker-compose run php composer install
```

Now you can start using all scripts and work with the code.

## Preparing Pull Request

There 3 rules will highly increase chance to get your PR merged:

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

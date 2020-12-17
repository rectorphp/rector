# How to Add Checkstyle to your CI?

[Checkstyle](https://github.com/staabm/annotate-pull-request-from-checkstyle) is feature for GitHub Actions to add comment right into your pull-request.

Save your time from looking into failed CI build, when you can see comment right in your pull-request.

## Add GitHub Actions Workflow

```yaml
# .github/workflows/rector_checkstyle.yaml
# see https://github.com/staabm/annotate-pull-request-from-checkstyle
name: Rector Checkstyle

on:
    pull_request: null
    push:
        branches:
            - master

jobs:
    rector_checkstyle:
        runs-on: ubuntu-latest
        steps:
            -   uses: actions/checkout@v2

            -   uses: shivammathur/setup-php@v2
                with:
                    php-version: 7.2
                    coverage: none
                    tools: cs2pr

            -   run: composer install --no-progress --ansi

            -   run: vendor/bin/rector process --ansi --dry-run --output-format=checkstyle | cs2pr
```

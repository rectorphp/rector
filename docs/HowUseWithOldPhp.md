### How to use on PHP < 7.1 on Incompatible Composer Dependencies

You must have an isolated environment with PHP 7.1 (for example in a Docker container). When you have it then run following command:

```
composer create-project rector/rector path-to-rector
```

You will be able to run all commands in the following manner:

```
path-to-rector/bin/rector process /var/www/old-project --config path-to-rector/src/config/level/symfony/symfony33.yml
# or for short
path-to-rector/bin/rector process /var/www/old-project --level symfony33
```

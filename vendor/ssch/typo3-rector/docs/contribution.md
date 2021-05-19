# Contributing

Want to help? Great!
Joing TYPO3 slack channel #ext-typo3-rector

## Fork the project

Fork this project into your own account.

## Install typo3-rector

Install the project using composer:
```bash
git clone git@github.com:your-account/typo3-rector.git
cd typo3-rector
composer install
```

## Pick an issue from the list

https://github.com/sabbelasichon/typo3-rector/issues You can filter by tags

## Assign the issue to yourself

Assign the issue to yourself so others can see that you are working on it.

## Create Rector

Run command and answer all questions properly
```bash
./vendor/bin/rector typo3-create
```

Afterwards you have to write your Rector and your tests for it.
If you need, you have to create so called stubs.
Stubs contain basically the skeleton of the classes you would like to refactor.
Have a look at the stubs folder.

Last but not least, register your file in the right config file under the config folder (Maybe not necessary anymore in the near future).

## All Tests must be Green

Make sure you have a test in place for your Rector

All unit tests must pass before submitting a pull request.

```bash
./vendor/bin/phpunit
```

## Submit your changes

Great, now you can submit your changes in a pull request



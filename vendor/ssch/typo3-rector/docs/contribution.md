## Table of Contents
1. [Examples in action](./examples_in_action.md)
1. [Overview of all rules](./all_rectors_overview.md)
1. [Installation](./installation.md)
1. [Configuration and Processing](./configuration_and_processing.md)
1. [Best practice guide](./best_practice_guide.md)
1. [Special rules](./special_rules.md)
1. [Beyond PHP - Entering the realm of FileProcessors](./beyond_php_file_processors.md)
1. [Limitations](./limitations.md)
1. [Contribution](./contribution.md)

# Contributing

Want to help? Great!
Joing TYPO3 slack channel [#ext-typo3-rector](https://typo3.slack.com/archives/C019R5LAA6A)

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

## Create an own Rector

Run command and answer all questions properly
```bash
./vendor/bin/rector typo3-generate
```

This command will ask you some questions to provide a proper rector setup.
Following this will lead to the creation of the overall rector structure necessay.
It will create the skeleton for the rector with the class, test class, fixtures and directories to start coding - basically everything you need to start!


### Useful infos:

- the `refactor` must return a node or null
- keep it flat! Use early returns (with null) in case your conditions for migration are not met
- the `getNodeTypes` method is used to define the use case of the function to migrate. It helps as well acting like an early return (see example below)
- helper functions and classes are provided via rector to make it easy for you to control further processing
- here is a list of all php node types: https://github.com/rectorphp/php-parser-nodes-docs/blob/master/README.md

### Example

In this example the methods `GeneralUtility::strtoupper(...)` and `GeneralUtility::strtolower(...)` are migrated.
- `getNodeTypes` checks for the StaticCall, preventing further rector execution if not met
- `refactor` first checks for the ObjectType to do an early return in case the class scanned is not `TYPO3\CMS\Core\Utility\GeneralUtility`
- after that the actual function call is checked for being one of the functions to migrate


```php
final class GeneralUtilityToUpperAndLowerRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType(
            $node,
            new ObjectType('TYPO3\CMS\Core\Utility\GeneralUtility')
        )) {
            return null;
        }

        if (! $this->isNames($node->name, ['strtoupper', 'strtolower'])) {
            return null;
        }
...
```



## All Tests must be Green

Make sure you have a test in place for your Rector

All unit tests must pass before submitting a pull request.

```bash
./vendor/bin/phpunit
```

Overall hints for testing:

- testing happens via fixture files (*.php.inc)
- those files display the code before and after execution, separated by `-----`
- rector keeps spaces etc. as its job is migration and not code cleaning, so keep that in mind
- provide custom test classes via "Source" folder, that will be tested, but *will not* be affected by your rector to to test and prevent side effects of your rule

## Submit your changes

Great, now you can submit your changes in a pull request



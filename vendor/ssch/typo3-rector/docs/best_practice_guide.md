# Best practice guide

## What to use for

You can use typo3-rector in various ways:
- checking existing code if there are left out segments of the last upgrade
- evaluate upgrades and see what parts of your custom setup will be updated automatically
- partial execution of full core upgrades


## Guide for a good upgrade


### Starting
Starting with an upgrade should start with installing typo3-rector and checking for the rector rules/sets of your current version, not the one you're targeting.
Often there are things that were missed out in previous upgrades while rector adds rulesets for those.


So if you're on TYPO3 v8 you should start with applying the rulesets for v7 first and v8 afterwards to make sure you're current code base.

Examples for often missed out upgrade steps:
- ext:lang key changes OR the full ext:lang replacement
- TCA renderType addition on type="single"
- TCA fieldwizard OR overrideChildTCA rewrite


### Ongoing upgrade

After making sure your current code base is properly upgraded you go on with the actual upgrade process.
This requires manual action like allowing the core versions in your composer.json and ext_emconf.php files depending on your individual setup.

The overall package upgrade is then followed by some more typo3-rector actions.

Depending on the amount of version steps you should add the ClassAliasMap as mentioned above for e.g. v8 if you're going from v8 to v10 directly.

Once again you add you're wanted/needed rulesets that should be separated by version.
It also comes in handy to divide between TCA and TYPO3 changes AND/OR you're packages.

**The TYPO3 sets always include the TCA sets!**

TCA changes are often not that big in their impact but necessary. Also custom packages do not necesserily provide that much own TCA.
Both of that is reason to gather multiple packages for a combined TCA run with the following config:


```php
    $containerConfigurator->import(Typo3SetList::TCA_95);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/packages/package_numberone',
        __DIR__ . '/packages/package_thesecond',
        __DIR__ . '/packages/package_thirdone',
    ]);
```

---
**Be aware!**
There are limitation to the TCA detection.

Typo3-rector can only detect TCA if there's a return statement along with a 'ctrl' and 'columns' key, just like here:

```php
    return [
        'ctrl' => [],
        'columns' => [],
    ];
```

Depending on your TCA, which can include overrides with array_replace_recursive() you have to manually adapt the TCA for the time of the typo3-rector process.
So all other migrations are done for you via ruleset.

---


The non-TCA rules are often a little more specific and should be applied in a separate step with the according set, e.g. `Typo3SetList::TYPO3_95`.

Those rules bring immense value as you don't have to find the replacement of classes and the actual changelog as it is provided for you already on execution.
With `--dry-run` you can process the ruleset without applying the changes giving you a perfect overview **before** changing your code.

You can focus on testing and possibly learning the new implementation of previous functions


## Special cases

There are changes that typo3-rector knows of but cannot fully complete.
That's why rector provides a HTML-Reporter which informs you about changes made and necessary steps to fully migrate.

```php
    $parameters->set(Typo3Option::REPORT_DIRECTORY,
        __DIR__ . '/typo3-rector-logs'
    );
```

An example for this would be the TCA change of replacing `'internal_type' => 'file'` with FAL.

The TCA is changed easy, but the whole DB record type changes as previously the whole name of the file was saved into the DB column, while now with FAL, it would only create an index-count - the reference to the new files that are saved in sys_file and connected via sys_file_reference.
(see [internal_type deprecation changelog](https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-86406-TCATypeGroupInternal_typeFileAndFile_reference.html))






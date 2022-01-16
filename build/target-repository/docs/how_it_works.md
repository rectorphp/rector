# How Does Rector Work?

(Inspired by [*How it works* in BetterReflection](https://github.com/Roave/BetterReflection/blob/master/docs/how-it-works.md))

## 1. Finds all files and Load Configured Rectors

- The application finds files in the source code you provide and registered Rectors - from  `--config` or local `rector.php`
- Then it iterates all found files and applies relevant Rectors to them.
- A *Rector* in this context is 1 single class that modifies 1 thing, e.g. changes the class name

## 2. Parse and Reconstruct 1 File

The iteration of files, nodes and Rectors respects this lifecycle:

```php
<?php

declare(strict_types=1);

use Rector\Contract\Rector\PhpRectorInterface;
use PhpParser\Parser;

/** @var SplFileInfo[] $fileInfos */
foreach ($fileInfos as $fileInfo) {
    // 1 file => nodes
    /** @var Parser $phpParser */
    $nodes = $phpParser->parse(file_get_contents($fileInfo->getRealPath()));

    // nodes => 1 node
    foreach ($nodes as $node) { // rather traverse all of them
        /** @var PhpRectorInterface[] $rectors */
        foreach ($rectors as $rector) {
            foreach ($rector->getNodeTypes() as $nodeType) {
                if (is_a($node, $nodeType, true)) {
                    $rector->refactor($node);
                }
            }
        }
    }
}
```

### 2.1 Prepare Phase

- Files are parsed by [`nikic/php-parser`](https://github.com/nikic/PHP-Parser), 4.0 that supports writing modified tree back to a file
- Then nodes (array of objects by parser) are traversed by `StandaloneTraverseNodeTraverser` to prepare their metadata, e.g. the class name, the method node the node is in, the namespace name etc. added by `$node->setAttribute('key', 'value')`.

### 2.2 Rectify Phase

- When all nodes are ready, the application iterates on all active Rectors
- Each node is compared with `$rector->getNodeTypes()` method to see if this Rector should do some work on it, e.g. is this class name called `OldClassName`?
- If it doesn't match, it goes to next node.
- If it matches, the `$rector->reconstruct($node)` method is called
- Active Rector change everything they have to and return changed nodes

### 2.2.1 Order of Rectors

- Nodes to run rectors are iterated in the node traversal order.

E.g. rectors for `Class_` node always run before rectors for `ClassMethod` in one class.

- Rectors are run by the natural order in the configuration, meaning the first
in the configuration will be run first.

E.g. in this case, first the `@expectedException` annotation will be changed to a method,
 then the `setExpectedException` method will be changed to `expectedException`.

```php
<?php
// rector.php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(Rector\PHPUnit\Rector\ClassMethod\ExceptionAnnotationRector::class);
    $services->set(Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)
        ->arg('$oldToNewMethodsByClass', [
             PHPUnit\Framework\TestClass::class => [
                'setExpectedException' => 'expectedException',
                'setExpectedExceptionRegExp' => 'expectedException',
            ],
        ]);
};
```

### 2.3 Save File/Diff Phase

- When work on all nodes of 1 file is done, the file will be saved if it has some changes
- Or if the `--dry-run` option is on, it will store the *git-like* diff thanks to [GeckoPackages/GeckoDiffOutputBuilder](https://github.com/GeckoPackages/GeckoDiffOutputBuilder)
- Then Rector will go to the next file

## 3 Reporting

- After this, Rector displays the list of changed files
- Or with `--dry-run` option the diff of these files

### Similar Projects

- [ClangMR](https://static.googleusercontent.com/media/research.google.com/en//pubs/archive/41342.pdf) for C++ by Google (closed source) - almost identical workflow, developed independently though
- [hhast](https://github.com/hhvm/hhast) - HHVM AST + format preserving + migrations
- [facebook/jscodeshift](https://github.com/facebook/jscodeshift) for Javascript
- [silverstripe/silverstripe-upgrader](https://github.com/silverstripe/silverstripe-upgrader) for PHP CMS, Silverstripe
- [dereuromark/upgrade](https://github.com/dereuromark/upgrade) for PHP Framework, CakePHP

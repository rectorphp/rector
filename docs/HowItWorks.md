# How Does Rector Work?

(Inspired by [*How it works* in BetterReflection](https://github.com/Roave/BetterReflection/blob/master/docs/how-it-works.md))

## 1. Finds all files and Load Configured Rectors

- The application finds files in source you provide and registeres Rectors - from `--level`, `--config` or local `rector.yaml`
- Then it iterates all found files and applies relevant Rectors to them.
- *Rector* in this context is 1 single class that modifies 1 thing, e.g. changes class name

## 2. Parse and Reconstruct 1 File

The iteration of files, nodes and Rectors respects this life cycle:

```php
<?php declare(strict_types=1);

use Rector\Contract\Rector\PhpRectorInterface;
use PhpParser\Parser;

/** @var SplFileInfo[] $fileInfos */
foreach ($fileInfos as $fileInfo) {
    // 1 file => nodes
    /** @var Parser $phpParser */
    $nodes = $phpParser->parse(file_get_contents($fileInfo->getRealPath());

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

- File is parsed by [`nikic/php-parser`](https://github.com/nikic/PHP-Parser), 4.0 that supports writing modified tree back to a file
- Then nodes (array of objects by parser) are traversed by `StandaloneTraverseNodeTraverser` to prepare it's metadata, e.g. class name, method node the node is in, namespace name etc. added by `$node->setAttribute(Attribute::CLASS_NODE, 'value')`.

### 2.2 Rectify Phase

- When all nodes are ready, applicies iterates all active Rector
- Each nodes is compared to `$rector->getNodeTypes()` method to see, if this Rector should do some work on it, e.g. is this class name called `OldClassName`?
- If it doesn't match, it goes to next node.
- If it matches, the `$rector->reconstruct($node)` method is called
- Active Rector changes all what he should and returns changed node

### 2.2.1 Order of Rectors

- Rectors are run by they natural order in config, first will be run first.

E.g. in this case, first will be changed `@expectedException` annotation to method,
 then a method `setExpectedException` to `expectedException`.

```yaml
# rector.yaml
services:
    Rector\PHPUnit\Rector\ExceptionAnnotationRector: ~

    Rector\Rector\MethodCall\MethodNameReplacerRector:
        $perClassOldToNewMethods:
                'PHPUnit\Framework\TestClass':
                    'setExpectedException': 'expectedException'
                    'setExpectedExceptionRegExp': 'expectedException'
```

### 2.3 Save File/Diff Phase

- When work on all nodes of 1 file is done, the file will be saved if it has some changes
- Or if the `--dry-run` option is on, it will store the *git-like* diff thanks to [GeckoPackages/GeckoDiffOutputBuilder](https://github.com/GeckoPackages/GeckoDiffOutputBuilder)
- Then go to next file

## 3 Reporting

- After this Rector displays list of changed files
- Or with `--dry-run` option the diff of these files

### Similar Projects

- [ClangMR](https://static.googleusercontent.com/media/research.google.com/en//pubs/archive/41342.pdf) for C++ by Google (closed source) - almost idential workflow, developed independently though
- [facebook/jscodeshift](https://github.com/facebook/jscodeshift) for Javascript
- [silverstripe/silverstripe-upgrader](https://github.com/silverstripe/silverstripe-upgrader) for PHP CMS, Silverstripe
- [dereuromark/upgrade](https://github.com/dereuromark/upgrade) for PHP Framework, CakePHP

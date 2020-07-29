# Generating your own Rector from a Recipe

## 1. Configure a Rector Recipe in `rector.php`

```php
<?php

declare(strict_types=1);

use PhpParser\Node\Expr\Assign;
use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::RECTOR_RECIPE, [
        'package' => 'Celebrity',
        'name' => 'SplitToExplodeRector',
        'node_types' => [
            Assign::class,
        ],
        'description' => 'Removes unneeded $a = $a assignments',
        'code_before' => <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    public function run()
    {
        $a = $a;
    }
}
CODE_SAMPLE,
        'code_after' => <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    public function run()
    {
    }
}
CODE_SAMPLE,
        // e.g. link to RFC or headline in upgrade guide, 1 or more in the list
        'source' => [
        ],
        // e.g. symfony30, target config to append this rector to
        'set' => SetList::CODE_QUALITY,
    ]);
};
```

## 2. Generate it

```bash
vendor/bin/rector create

# or for short
vendor/bin/rector c
```

That's it :)

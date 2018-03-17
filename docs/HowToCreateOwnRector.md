## 6 Steps to Add New Rector

In case you need a transformation that you didn't find in Dynamic Rectors, you can create your own:

1. Just extend `Rector\Rector\AbstractRector` class. It will prepare **2 methods**:

```php
public function isCandidate(Node $node): bool
{
}

public function refactor(Node $node): ?Node
{
}
```

2. Put it under `namespace Rector\Contrib\<set>;` namespace

```php
<?php declare(strict_types=1);

namespace Rector\Contrib\Symfony;

use Rector\Rector\AbstractRector;

final class MyRector extends AbstractRector
{
    // ...
}
```

3. Add a Test Case - [see PHPUnit example](https://github.com/rectorphp/rector/blob/master/tests/Rector/Contrib/PHPUnit/ExceptionAnnotationRector/Test.php)

4. Add to specific level, e.g. [`/src/config/levels/symfony/symfony33.yml`](/src/config/levels/symfony/symfony33.yml)

5. Submit PR

6. :+1:

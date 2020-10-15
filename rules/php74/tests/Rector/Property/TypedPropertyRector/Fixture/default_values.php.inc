<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValues
{
    /**
     * @var float
     */
    private $a = 42.42;

    private $b = 42;

    private $c = 'hey';

    private $e = 42.42;

    private $f = 42;

    /**
     * @var array
     */
    private $g = [1, 2, 3];

    /**
     * @var iterable
     */
    private $h = [1, 2, 3];
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class DefaultValues
{
    private float $a = 42.42;

    private int $b = 42;

    private string $c = 'hey';

    private float $e = 42.42;

    private int $f = 42;

    private array $g = [1, 2, 3];

    private iterable $h = [1, 2, 3];
}

?>

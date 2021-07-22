<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use JetBrains\PhpStorm\Immutable;

final class Coordinate
{
    #[Immutable]
    public float $latitude;

    public function __construct(float $latitude)
    {
        $this->latitude = $latitude;
    }
}
?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

use JetBrains\PhpStorm\Immutable;

final class Coordinate
{
    public function __construct(#[Immutable] public float $latitude)
    {
    }
}
?>

<?php

namespace Rector\Core\Tests\Issues\DowngradeReadonlyClassPropertyToPhp80\Fixture;

final readonly class Fixture
{
    public function __construct(public array $property)
    {
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\DowngradeReadonlyClassPropertyToPhp80\Fixture;

final class Fixture
{
    public function __construct(public array $property)
    {
    }
}

?>

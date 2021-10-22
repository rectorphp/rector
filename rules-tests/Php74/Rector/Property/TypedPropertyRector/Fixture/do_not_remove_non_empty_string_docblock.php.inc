<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveNonEmptyStringDocblock
{
    /**
     * @var string
     * @psalm-var non-empty-string
     */
    private $name;
}
?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class DoNotRemoveNonEmptyStringDocblock
{
    /**
     * @psalm-var non-empty-string
     */
    private string $name;
}
?>

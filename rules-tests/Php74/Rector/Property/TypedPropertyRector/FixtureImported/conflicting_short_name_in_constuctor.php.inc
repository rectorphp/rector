<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\ConflictingName;
use Rector;

final class ConflictingShortNameInConstructor
{
    /**
     * @var Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Nested\ConflictingName
     */
    private $firstOne;

    public function __construct(ConflictingName $conflictingName)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\ConflictingName;
use Rector;

final class ConflictingShortNameInConstructor
{
    private \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Nested\ConflictingName $firstOne;

    public function __construct(ConflictingName $conflictingName)
    {
    }
}

?>

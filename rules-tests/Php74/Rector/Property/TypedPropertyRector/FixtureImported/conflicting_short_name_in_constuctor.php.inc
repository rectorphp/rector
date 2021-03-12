<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\ConflictingName;
use Rector;

final class ConflictingShortNameInConstructor
{
    /**
     * @var Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\Nested\ConflictingName
     */
    private $firstOne;

    public function __construct(ConflictingName $conflictingName)
    {
    }
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\ConflictingName;
use Rector;

final class ConflictingShortNameInConstructor
{
    private \Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\Nested\ConflictingName $firstOne;

    public function __construct(ConflictingName $conflictingName)
    {
    }
}

?>

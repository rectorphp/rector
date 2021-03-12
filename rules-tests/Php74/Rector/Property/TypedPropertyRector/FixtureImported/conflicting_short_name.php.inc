<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\ConflictingName;
use Rector;

final class ConflictingShortName
{
    /**
     * @var ConflictingName
     */
    private $firstOne;

    /**
     * @var Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Nested\ConflictingName
     */
    private $secondOne;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\ConflictingName;
use Rector;

final class ConflictingShortName
{
    private ConflictingName $firstOne;

    private \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\Nested\ConflictingName $secondOne;
}

?>

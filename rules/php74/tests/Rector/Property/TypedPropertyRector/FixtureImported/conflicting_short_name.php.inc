<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\ConflictingName;
use Rector;

final class ConflictingShortName
{
    /**
     * @var ConflictingName
     */
    private $firstOne;

    /**
     * @var Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\Nested\ConflictingName
     */
    private $secondOne;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\ConflictingName;
use Rector;

final class ConflictingShortName
{
    private ConflictingName $firstOne;

    private \Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\Nested\ConflictingName $secondOne;
}

?>

<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\ConflictingName;

final class AlreadyImportedShortName
{
    /**
     * @var ConflictingName
     */
    private $conflictingName;

    /**
     * @param ConflictingName $conflictingName
     */
    public function __construct(ConflictingName $conflictingName)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\ConflictingName;

final class AlreadyImportedShortName
{
    private ConflictingName $conflictingName;

    /**
     * @param ConflictingName $conflictingName
     */
    public function __construct(ConflictingName $conflictingName)
    {
    }
}

?>

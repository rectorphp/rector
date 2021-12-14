<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureImported;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass2 as Alias;

final class ConflictWithAlias3
{
    /**
     * @var AnotherClass|\stdClass
     */
    private $anotherClass;

    public function __construct(AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }

    public function setStdClass()
    {
        $this->anotherClass = new \stdClass;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureImported;

use stdClass;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;
use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass2 as Alias;

final class ConflictWithAlias3
{
    private AnotherClass|stdClass $anotherClass;

    public function __construct(AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }

    public function setStdClass()
    {
        $this->anotherClass = new stdClass;
    }
}

?>

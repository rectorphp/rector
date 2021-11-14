<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureClassLikeTypeOnly;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AnotherClass;

final class SkipNullableVarUnion
{
    /**
     * @var null|AnotherClass|\stdClass
     */
    private $anotherClass;

    public function __construct(?AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }

    public function setStdClass()
    {
        $this->anotherClass = new \stdClass;
    }
}

?>

<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class SetIfElse
{
    protected $stringOrInteger = 'hi';

    public function setNumber()
    {
        if (mt_rand(0, 100)) {
            $this->stringOrInteger = 'hey';
        } else {
            $this->stringOrInteger = 1000;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureUnionIntersectionTypes;

final class SetIfElse
{
    protected int|string $stringOrInteger = 'hi';

    public function setNumber()
    {
        if (mt_rand(0, 100)) {
            $this->stringOrInteger = 'hey';
        } else {
            $this->stringOrInteger = 1000;
        }
    }
}

?>

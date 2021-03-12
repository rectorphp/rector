<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureUnionTypes;

class SetIfElse
{
    public $stringOrInteger = 'hi';

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

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureUnionTypes;

class SetIfElse
{
    public string|int $stringOrInteger = 'hi';

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

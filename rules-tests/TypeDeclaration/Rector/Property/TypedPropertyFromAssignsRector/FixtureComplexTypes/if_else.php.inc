<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\FixtureComplexTypes;

final class IfElse
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

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\FixtureComplexTypes;

final class IfElse
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

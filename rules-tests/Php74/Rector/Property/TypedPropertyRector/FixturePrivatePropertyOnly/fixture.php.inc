<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixturePrivatePropertyOnly;

class Fixture
{
    /**
     * @var bool
     */
    private $privateProperty;

    /**
     * @var bool
     */
    protected $protectedProperty;
}

class ExtendsFixture extends Fixture
{
    /**
     * @var bool
     */
    protected $protectedProperty;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixturePrivatePropertyOnly;

class Fixture
{
    private bool $privateProperty;

    /**
     * @var bool
     */
    protected $protectedProperty;
}

class ExtendsFixture extends Fixture
{
    /**
     * @var bool
     */
    protected $protectedProperty;
}

?>

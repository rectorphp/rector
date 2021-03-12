<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector\Fixture;

class Fixture
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function getIsActive()
    {
        return $this->isActive;
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector\Fixture;

class Fixture
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function isActive()
    {
        return $this->isActive;
    }
}

?>

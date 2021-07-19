<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class MichaelTest
{
    /**
     * @var int
     */
    private $count;
    
    public function __construct()
    {
        $this->count = 123;
    }

    public function getCount():int
    {
        return $this->count;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class MichaelTest
{
    private int $count;
    
    public function __construct()
    {
        $this->count = 123;
    }

    public function getCount():int
    {
        return $this->count;
    }
}

?>

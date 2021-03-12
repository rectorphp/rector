<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

class PropertyWithDoc
{
    /**
     * @var int[]
     */
    private $fail;

    public function run()
    {
        return count($this->fail);
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

class PropertyWithDoc
{
    /**
     * @var int[]
     */
    private $fail;

    public function run()
    {
        return count((array) $this->fail);
    }
}

?>

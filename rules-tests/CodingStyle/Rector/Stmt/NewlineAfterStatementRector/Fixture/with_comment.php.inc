<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class WithComment
{
    /**
     * @var string
     */
    private $property;
    /**
     * @var string
     */
    private $property2;
    /**
     * a method
     */
    public function run()
    {
    }
    /**
     * a method
     */
    public function run2()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\Fixture;

class WithComment
{
    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $property2;

    /**
     * a method
     */
    public function run()
    {
    }

    /**
     * a method
     */
    public function run2()
    {
    }
}

?>

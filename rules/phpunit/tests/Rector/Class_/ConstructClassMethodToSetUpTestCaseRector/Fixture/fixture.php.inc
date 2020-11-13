<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someValue;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $this->someValue = 1000;
        parent::__construct($name, $data, $dataName);
    }
}

?>
-----
<?php

namespace Rector\PHPUnit\Tests\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->someValue = 1000;
    }
    private $someValue;
}

?>

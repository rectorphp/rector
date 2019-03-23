<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Source\DummyObjectBehavior;

class KeepMethodSpec extends DummyObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);

        $result = $this->getSomeNumbers();
    }

    private function getSomeNumbers()
    {
        return [1, 2, 3];
    }
}

?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture;

use Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Source\DummyObjectBehavior;

class KeepMethodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture\KeepMethod
     */
    private $keepMethod;
    protected function setUp()
    {
        $this->keepMethod = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\Fixture\KeepMethod(5);

        $result = $this->getSomeNumbers();
    }

    private function getSomeNumbers()
    {
        return [1, 2, 3];
    }
}

?>

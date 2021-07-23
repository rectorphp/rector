<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\DoubleImportedTraitUse\Fixture;

use PHPStan\Testing\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

final class AddArrayTrait extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $client;

    protected function setUp(): void
    {
        $this->client = $this->prophesize(stdClass::class);
    }

    /**
     * @dataProvider invalidValues
     */
    public function testInvalidValues(int | array $value): void
    {
    }

    public function invalidValues()
    {
        yield 'Class without __toString not possible' => [1000];
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\DoubleImportedTraitUse\Fixture;

use PHPStan\Testing\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

final class AddArrayTrait extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $client;

    protected function setUp(): void
    {
        $this->client = $this->prophesize(stdClass::class);
    }

    /**
     * @dataProvider invalidValues
     * @param int $value
     */
    public function testInvalidValues(int | array $value): void
    {
    }

    public function invalidValues()
    {
        yield 'Class without __toString not possible' => [1000];
    }
}

?>

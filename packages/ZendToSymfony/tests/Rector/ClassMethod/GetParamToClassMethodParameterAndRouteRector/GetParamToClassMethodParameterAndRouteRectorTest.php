<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\ClassMethod\GetParamToClassMethodParameterAndRouteRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\ZendToSymfony\Rector\ClassMethod\GetParamToClassMethodParameterAndRouteRector;

final class GetParamToClassMethodParameterAndRouteRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/get_param_to_request.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return GetParamToClassMethodParameterAndRouteRector::class;
    }
}

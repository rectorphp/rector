<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor;

use Iterator;
use Rector\NetteToSymfony\Rector\ClassMethod\RouterListToControllerAnnotationsRector;
use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\Route;
use Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\Source\RouteList;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RouterListToControllerAnnotationsRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/new_route_to_annotation.php.inc'];
        yield [__DIR__ . '/Fixture/static_route_to_annotation.php.inc'];
        yield [__DIR__ . '/Fixture/constant_reference_route_to_annotation.php.inc'];
        yield [__DIR__ . '/Fixture/method_named_routes.php.inc'];
        yield [__DIR__ . '/Fixture/general_method_named_routes.php.inc'];
        yield [__DIR__ . '/Fixture/with_parameter.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RouterListToControllerAnnotationsRector::class => [
                '$routeListClass' => RouteList::class,
                '$routerClass' => Route::class,
            ],
        ];
    }
}

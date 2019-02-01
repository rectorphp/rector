<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor;

use Rector\NetteToSymfony\Rector\RouterListToControllerAnnotationsRector;
use Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Source\Route;
use Rector\NetteToSymfony\Tests\Rector\MethodCall\RouterListToControllerAnnotationsRetor\Source\RouteList;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RouterListToControllerAnnotationsRetorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/new_route_to_annotation.php.inc',
            __DIR__ . '/Fixture/static_route_to_annotation.php.inc',
            __DIR__ . '/Fixture/constant_reference_route_to_annotation.php.inc',
            __DIR__ . '/Fixture/method_named_routes.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RouterListToControllerAnnotationsRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            '$routeListClass' => RouteList::class,
            '$routerClass' => Route::class,
        ];
    }
}

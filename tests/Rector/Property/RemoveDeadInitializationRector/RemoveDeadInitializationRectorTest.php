<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Property\RemoveDeadInitializationRector;

use Rector\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Rector\Property\RemoveDeadInitializationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

class RemoveDeadInitializationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RemoveDeadInitializationRector::class => [
            ],
        ];
    }
}

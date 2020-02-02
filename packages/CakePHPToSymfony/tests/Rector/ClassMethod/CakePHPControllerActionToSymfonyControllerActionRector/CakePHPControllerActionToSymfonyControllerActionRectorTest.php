<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\ClassMethod\CakePHPControllerActionToSymfonyControllerActionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPControllerActionToSymfonyControllerActionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return CakePHPControllerActionToSymfonyControllerActionRector::class;
    }
}

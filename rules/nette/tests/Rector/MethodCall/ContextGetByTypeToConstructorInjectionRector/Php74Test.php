<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Nette\Rector\MethodCall\ContextGetByTypeToConstructorInjectionRector;

final class Php74Test extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.4
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePhp74');
    }

    protected function getRectorClass(): string
    {
        return ContextGetByTypeToConstructorInjectionRector::class;
    }
}

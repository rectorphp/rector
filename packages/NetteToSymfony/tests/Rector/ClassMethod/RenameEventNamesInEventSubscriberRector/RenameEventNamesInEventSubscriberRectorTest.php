<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector;

use Iterator;
use Rector\NetteToSymfony\Rector\ClassMethod\RenameEventNamesInEventSubscriberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameEventNamesInEventSubscriberRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/presenter_startup.php.inc'];
        yield [__DIR__ . '/Fixture/event_class.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return RenameEventNamesInEventSubscriberRector::class;
    }
}

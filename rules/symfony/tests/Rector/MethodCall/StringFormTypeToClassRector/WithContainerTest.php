<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\StringFormTypeToClassRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Symfony\Rector\MethodCall\StringFormTypeToClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class WithContainerTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureWithContainer');
    }

    protected function setParameter(string $name, $value): void
    {
        parent::setParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ . '/Source/custom_container.xml');
    }

    protected function getRectorClass(): string
    {
        return StringFormTypeToClassRector::class;
    }
}

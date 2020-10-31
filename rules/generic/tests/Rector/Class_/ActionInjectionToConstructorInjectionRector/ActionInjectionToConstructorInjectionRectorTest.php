<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Class_\ActionInjectionToConstructorInjectionRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Generic\Rector\Class_\ActionInjectionToConstructorInjectionRector;
use Rector\Generic\Rector\Variable\ReplaceVariableByPropertyFetchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ActionInjectionToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->setParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ . '/xml/services.xml');

        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ActionInjectionToConstructorInjectionRector::class => [],
            ReplaceVariableByPropertyFetchRector::class => [],
        ];
    }
}

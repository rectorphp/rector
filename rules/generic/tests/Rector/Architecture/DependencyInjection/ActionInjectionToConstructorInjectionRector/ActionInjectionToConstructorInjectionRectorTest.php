<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector;
use Rector\Generic\Rector\Architecture\DependencyInjection\ReplaceVariableByPropertyFetchRector;
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ActionInjectionToConstructorInjectionRector::class => [],
            ReplaceVariableByPropertyFetchRector::class => [],
        ];
    }
}

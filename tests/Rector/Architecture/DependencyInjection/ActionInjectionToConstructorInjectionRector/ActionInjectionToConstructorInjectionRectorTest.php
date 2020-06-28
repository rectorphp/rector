<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector;
use Rector\Core\Rector\Architecture\DependencyInjection\ReplaceVariableByPropertyFetchRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
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

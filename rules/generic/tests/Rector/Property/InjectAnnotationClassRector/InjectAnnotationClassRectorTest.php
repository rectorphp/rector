<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Property\InjectAnnotationClassRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InjectAnnotationClassRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->setParameter(
            Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
            __DIR__ . '/../../../../../symfony/tests/Rector/MethodCall/GetToConstructorInjectionRector/xml/services.xml'
        );

        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function provideConfigFileInfo(): ?SmartFileInfo
    {
        return new SmartFileInfo(__DIR__ . '/config/configured_rule.php');
    }
}

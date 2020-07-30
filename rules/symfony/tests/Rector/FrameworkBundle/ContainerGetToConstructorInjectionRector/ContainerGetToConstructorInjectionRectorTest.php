<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;
use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentCommand;
use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ThisClassCallsMethodInConstructor;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ContainerGetToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->setParameter(
            Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
            __DIR__ . '/../GetToConstructorInjectionRector/xml/services.xml'
        );
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
            ContainerGetToConstructorInjectionRector::class => [
                ContainerGetToConstructorInjectionRector::CONTAINER_AWARE_PARENT_TYPES => [
                    ContainerAwareParentClass::class,
                    ContainerAwareParentCommand::class,
                    ThisClassCallsMethodInConstructor::class,
                ],
            ],
        ];
    }
}

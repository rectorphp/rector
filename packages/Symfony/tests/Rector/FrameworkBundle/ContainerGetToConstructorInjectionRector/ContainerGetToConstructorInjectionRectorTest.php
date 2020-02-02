<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector;

use Iterator;
use Rector\Configuration\Option;
use Rector\Symfony\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;
use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentCommand;
use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ThisClassCallsMethodInConstructor;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ContainerGetToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->setParameter(
            Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
            __DIR__ . '/../GetToConstructorInjectionRector/xml/services.xml'
        );
        $this->doTestFile($file);
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
                '$containerAwareParentTypes' => [
                    ContainerAwareParentClass::class,
                    ContainerAwareParentCommand::class,
                    ThisClassCallsMethodInConstructor::class,
                ],
            ],
        ];
    }
}

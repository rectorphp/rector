<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector;

use DI\Annotation\Inject as PHPDIInject;
use Iterator;
use JMS\DiExtraBundle\Annotation\Inject;
use Rector\Configuration\Option;
use Rector\Rector\Property\InjectAnnotationClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class InjectAnnotationClassRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->setParameter(
            Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
            __DIR__ . '/../../../../packages/Symfony/tests/Rector/FrameworkBundle/GetToConstructorInjectionRector/xml/services.xml'
        );

        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            InjectAnnotationClassRector::class => [
                '$annotationClasses' => [Inject::class, PHPDIInject::class],
            ],
        ];
    }
}

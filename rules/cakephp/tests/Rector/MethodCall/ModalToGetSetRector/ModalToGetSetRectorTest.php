<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

use Iterator;
use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source\SomeModelType;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ModalToGetSetRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ModalToGetSetRector::class => [
                ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => [
                    new ModalToGetSet(SomeModelType::class, 'config', null, null, 2, 'array'),
                    new ModalToGetSet(
                        SomeModelType::class,
                        'customMethod',
                        'customMethodGetName',
                        'customMethodSetName',
                        2,
                        'array'
                    ),
                    new ModalToGetSet(SomeModelType::class, 'makeEntity', 'createEntity', 'generateEntity'),
                    new ModalToGetSet(SomeModelType::class, 'method'),
                ],
            ],
        ];
    }
}

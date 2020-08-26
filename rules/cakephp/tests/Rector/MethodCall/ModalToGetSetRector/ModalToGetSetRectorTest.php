<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

use Iterator;
use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source\SomeModelType;
use Rector\CakePHP\ValueObject\UnprefixedMethodToGetSet;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ModalToGetSetRector::class => [
                ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => [
                    new UnprefixedMethodToGetSet(SomeModelType::class, 'config', null, null, 2, 'array'),
                    new UnprefixedMethodToGetSet(
                        SomeModelType::class,
                        'customMethod',
                        'customMethodGetName',
                        'customMethodSetName',
                        2,
                        'array'
                    ),
                    new UnprefixedMethodToGetSet(
                        SomeModelType::class,
                        'makeEntity',
                        'createEntity',
                        'generateEntity'
                    ),
                    new UnprefixedMethodToGetSet(SomeModelType::class, 'method'),
                ],
            ],
        ];
    }
}

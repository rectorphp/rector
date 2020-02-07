<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector;

use Iterator;
use Rector\Core\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;

final class AddReturnTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
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
            AddReturnTypeDeclarationRector::class => [
                '$typehintForMethodByClass' => [
                    'Rector\Core\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\SomeClass' => [
                        'parse' => 'array',
                        'resolve' => 'SomeType',
                        'nullable' => '?SomeType',
                        'clear' => '',
                    ],
                    PHPUnitTestCase::class => [
                        'tearDown' => 'void',
                    ],
                ],
            ],
        ];
    }
}

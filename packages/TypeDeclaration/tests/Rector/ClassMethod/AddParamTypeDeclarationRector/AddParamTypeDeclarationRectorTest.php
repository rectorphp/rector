<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;

final class AddParamTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddParamTypeDeclarationRector::class => [
                '$typehintForParameterByMethodByClass' => [
                    ParentInterfaceWithChangeTypeInterface::class => [
                        'process' => [
                            0 => 'string',
                        ],
                    ],
                ],
            ],
        ];
    }
}

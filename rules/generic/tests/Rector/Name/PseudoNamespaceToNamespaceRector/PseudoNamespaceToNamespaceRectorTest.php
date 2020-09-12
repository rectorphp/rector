<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Name\PseudoNamespaceToNamespaceRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\Name\PseudoNamespaceToNamespaceRector;
use Rector\Generic\ValueObject\PseudoNamespaceToNamespace;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PseudoNamespaceToNamespaceRectorTest extends AbstractRectorTestCase
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
            PseudoNamespaceToNamespaceRector::class => [
                PseudoNamespaceToNamespaceRector::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES => [
                    new PseudoNamespaceToNamespace('PHPUnit_', ['PHPUnit_Framework_MockObject_MockObject']),
                    new PseudoNamespaceToNamespace('ChangeMe_', ['KeepMe_']),
                    new PseudoNamespaceToNamespace(
                        'Rector_Generic_Tests_Rector_Name_PseudoNamespaceToNamespaceRector_Fixture_'
                    ),
                ],
            ],
        ];
    }
}

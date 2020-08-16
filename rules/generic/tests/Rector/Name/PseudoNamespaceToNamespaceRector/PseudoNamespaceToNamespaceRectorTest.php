<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\Name\PseudoNamespaceToNamespaceRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\Name\PseudoNamespaceToNamespaceRector;
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
                    // namespace prefix => excluded classes
                    'PHPUnit_' => ['PHPUnit_Framework_MockObject_MockObject'],
                    'ChangeMe_' => ['KeepMe_'],
                    'Rector_Generic_Tests_Rector_Name_PseudoNamespaceToNamespaceRector_Fixture_' => [],
                ],
            ],
        ];
    }
}

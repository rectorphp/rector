<?php

declare(strict_types=1);

namespace Rector\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector;

use Iterator;
use Rector\Rector\Namespace_\PseudoNamespaceToNamespaceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PseudoNamespaceToNamespaceRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            PseudoNamespaceToNamespaceRector::class => [
                '$namespacePrefixesWithExcludedClasses' => [
                    // namespace prefix => excluded classes
                    'PHPUnit_' => ['PHPUnit_Framework_MockObject_MockObject'],
                    'ChangeMe_' => ['KeepMe_'],
                    'Rector_Tests_Rector_Namespace__PseudoNamespaceToNamespaceRector_Fixture_' => [],
                ],
            ],
        ];
    }
}

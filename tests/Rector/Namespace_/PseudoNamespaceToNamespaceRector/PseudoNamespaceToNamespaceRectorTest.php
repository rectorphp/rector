<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector;

use Iterator;
use Rector\Core\Rector\Namespace_\PseudoNamespaceToNamespaceRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class PseudoNamespaceToNamespaceRectorTest extends AbstractRectorTestCase
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
            PseudoNamespaceToNamespaceRector::class => [
                '$namespacePrefixesWithExcludedClasses' => [
                    // namespace prefix => excluded classes
                    'PHPUnit_' => ['PHPUnit_Framework_MockObject_MockObject'],
                    'ChangeMe_' => ['KeepMe_'],
                    'Rector_Core_Tests_Rector_Namespace__PseudoNamespaceToNamespaceRector_Fixture_' => [],
                ],
            ],
        ];
    }
}

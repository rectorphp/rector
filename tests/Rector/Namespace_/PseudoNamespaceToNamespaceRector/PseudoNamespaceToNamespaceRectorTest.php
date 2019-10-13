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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/fixture4.php.inc'];
        yield [__DIR__ . '/Fixture/fixture5.php.inc'];
        yield [__DIR__ . '/Fixture/fixture6.php.inc'];
        yield [__DIR__ . '/Fixture/var_doc.php.inc'];
        yield [__DIR__ . '/Fixture/use_statement.php.inc'];
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

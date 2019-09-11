<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector;

use Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NonNamespacedTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/NonNamespaced/simple.php.inc',
            __DIR__ . '/Fixture/NonNamespaced/already_imported.php.inc',
            __DIR__ . '/Fixture/NonNamespaced/function_import.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ImportFullyQualifiedNamesRector::class;
    }
}

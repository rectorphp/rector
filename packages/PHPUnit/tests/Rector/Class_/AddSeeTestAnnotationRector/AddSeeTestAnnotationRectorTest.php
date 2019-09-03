<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\AddSeeTestAnnotationRector;

use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddSeeTestAnnotationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/different_namespace.php.inc',
            __DIR__ . '/Fixture/add_to_doc_block.php.inc',
            // skip
            __DIR__ . '/Fixture/skip_existing.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AddSeeTestAnnotationRector::class;
    }
}

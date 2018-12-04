<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertFalseStrposToContainsRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertFalseStrposToContainsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertFalseStrposToContainsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return AssertFalseStrposToContainsRector::class;
    }
}

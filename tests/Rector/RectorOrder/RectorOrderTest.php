<?php declare(strict_types=1);

namespace Rector\Tests\Rector\RectorOrder;

use Rector\PHPUnit\Rector\SpecificMethod\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertSameBoolNullToSpecificMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Covers https://github.com/rectorphp/rector/pull/266#issuecomment-355725764
 */
final class RectorOrderTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        // order matters
        return [
            AssertComparisonToSpecificMethodRector::class => [],
            AssertSameBoolNullToSpecificMethodRector::class => [],
            AssertFalseStrposToContainsRector::class => [],
        ];
    }
}

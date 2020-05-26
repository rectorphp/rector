<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteKdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector;

final class ReplaceMagicPropertyEventWithEventClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/fixture.php.inc');

        $expectedEventFilePath = dirname($this->originalTempFile) . '/Event/FileManagerUploadEvent.php';
        $this->assertFileExists($expectedEventFilePath);
        $this->assertFileEquals(__DIR__ . '/Source/ExpectedClass.php', $expectedEventFilePath);
    }

    protected function getRectorClass(): string
    {
        return ReplaceMagicPropertyEventWithEventClassRector::class;
    }
}

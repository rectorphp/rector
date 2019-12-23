<?php

declare(strict_types=1);

namespace Rector\Tests\Issues\Issue2451;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue2451Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->markTestSkipped('Resolve after removing shifter');

        $this->doTestFile(__DIR__ . '/Fixture/fixture2451.php.inc');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}

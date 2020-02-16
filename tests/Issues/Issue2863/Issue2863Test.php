<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue2863;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue2863Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/fixture2863.php.inc');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config2863.yaml';
    }
}

<?php declare(strict_types=1);

namespace Rector\Tests\Issues\Issue835;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue835Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture835.php']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/config835.yml';
    }
}

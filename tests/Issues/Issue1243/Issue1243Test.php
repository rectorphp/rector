<?php declare(strict_types=1);

namespace Rector\Tests\Issues\Issue1243;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue1243Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture1243.php']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../config/level/twig/twig-underscore-to-namespace.yaml';
    }
}

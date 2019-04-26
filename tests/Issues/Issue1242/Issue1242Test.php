<?php declare(strict_types=1);

namespace Rector\Tests\Issues\Issue1242;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue1242Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture1242.php']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../config/level/twig/twig-underscore-to-namespace.yaml';
    }
}

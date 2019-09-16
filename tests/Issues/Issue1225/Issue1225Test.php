<?php declare(strict_types=1);

namespace Rector\Tests\Issues\Issue1225;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue1225Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/fixture1225.php.inc');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../config/set/twig/twig-underscore-to-namespace.yaml';
    }
}

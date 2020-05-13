<?php

declare(strict_types=1);

namespace Rector\PSR4\Tests\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see \Rector\PSR4\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector
 */
final class NormalizeNamespaceByPSR4ComposerAutoloadRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/normalize_file.php.inc');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config/normalize_namespace_config.yaml';
    }
}

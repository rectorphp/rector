<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector;

use Rector\Symfony\Rector\DependencyInjection\ContainerBuilderCompileEnvArgumentRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ContainerBuilderCompileEnvArgumentRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return ContainerBuilderCompileEnvArgumentRector::class;
    }
}

<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector;

use Rector\Symfony\Rector\Process\ProcessBuilderGetProcessRector;
use Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector\Source\ProcessBuilder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ProcessBuilderGetProcessRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ProcessBuilderGetProcessRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['$processBuilderClass' => ProcessBuilder::class];
    }
}

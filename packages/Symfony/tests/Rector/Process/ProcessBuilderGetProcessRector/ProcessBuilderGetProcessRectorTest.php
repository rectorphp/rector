<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector;

use Rector\Symfony\Rector\Process\ProcessBuilderGetProcessRector;
use Rector\Symfony\Tests\Rector\Process\ProcessBuilderGetProcessRector\Source\ProcessBuilder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ProcessBuilderGetProcessRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [ProcessBuilderGetProcessRector::class => ['$processBuilderClass' => ProcessBuilder::class]];
    }
}

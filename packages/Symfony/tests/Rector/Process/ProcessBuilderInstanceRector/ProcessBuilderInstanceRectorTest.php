<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Process\ProcessBuilderInstanceRector;

use Rector\Symfony\Rector\Process\ProcessBuilderInstanceRector;
use Rector\Symfony\Tests\Rector\Process\ProcessBuilderInstanceRector\Source\ProcessBuilder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ProcessBuilderInstanceRectorTest extends AbstractRectorTestCase
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
        return [ProcessBuilderInstanceRector::class => ['$processBuilderClass' => ProcessBuilder::class]];
    }
}

<?php declare(strict_types=1);

namespace Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverDocBlockRector
 */
final class ValueObjectRemoverDocBlockRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Wrong/wrong2.php.inc', __DIR__ . '/Wrong/wrong3.php.inc']
        );
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}

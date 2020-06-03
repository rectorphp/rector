<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Argument\ArgumentDefaultValueReplacerRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class Symfony28Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureSymfony28');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../../config/set/symfony/symfony28.yaml';
    }
}

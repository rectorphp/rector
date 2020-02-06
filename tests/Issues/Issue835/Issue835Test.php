<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue835;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue835Test extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../config/set/cakephp/cakephp34.yaml';
    }
}

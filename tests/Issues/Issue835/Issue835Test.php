<?php

declare(strict_types=1);

namespace Rector\Tests\Issues\Issue835;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue835Test extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture835.php.inc'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/../../../config/set/cakephp/cakephp34.yaml';
    }
}

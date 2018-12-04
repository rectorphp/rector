<?php declare(strict_types=1);

namespace Rector\YamlRector\Tests\Rector\ReplaceValueYamlRector;

use Iterator;
use Rector\YamlRector\Tests\AbstractYamlRectorTest;

final class ReplaceValueYamlRectorTest extends AbstractYamlRectorTest
{
    /**
     * @dataProvider provideFiles()
     */
    public function test(string $wrong, string $fixed): void
    {
        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    public function provideFiles(): Iterator
    {
        yield [__DIR__ . '/wrong/wrong.yml', __DIR__ . '/correct/correct.yml'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}

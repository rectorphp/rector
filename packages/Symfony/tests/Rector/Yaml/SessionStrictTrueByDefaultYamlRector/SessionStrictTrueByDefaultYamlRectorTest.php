<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Yaml\SessionStrictTrueByDefaultYamlRector;

use Iterator;
use Rector\YamlRector\Tests\AbstractYamlRectorTest;

/**
 * @covers \Rector\Symfony\Rector\Yaml\SessionStrictTrueByDefaultYamlRector
 */
final class SessionStrictTrueByDefaultYamlRectorTest extends AbstractYamlRectorTest
{
    /**
     * @dataProvider provideWrongToFixedFiles()
     */
    public function test(string $wrong, string $fixed): void
    {
        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    public function provideWrongToFixedFiles(): Iterator
    {
        yield [__DIR__ . '/Wrong/wrong.yml', __DIR__ . '/Correct/correct.yml'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}

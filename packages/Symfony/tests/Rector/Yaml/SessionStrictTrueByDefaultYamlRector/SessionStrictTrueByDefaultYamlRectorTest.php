<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Yaml\SessionStrictTrueByDefaultYamlRector;

use Iterator;
use Rector\YamlRector\Tests\AbstractYamlRectorTest;

final class SessionStrictTrueByDefaultYamlRectorTest extends AbstractYamlRectorTest
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
        yield [__DIR__ . '/Wrong/wrong.yml', __DIR__ . '/Correct/correct.yml'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}

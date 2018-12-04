<?php declare(strict_types=1);

namespace Rector\YamlRector\Tests\Rector\RenameSubKeyYamlRector;

use Iterator;
use Rector\YamlRector\Tests\AbstractYamlRectorTest;

/**
 * @covers \Rector\YamlRector\Rector\RenameSubKeyYamlRector
 */
final class RenameSubKeyYamlRectorTest extends AbstractYamlRectorTest
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
        yield [__DIR__ . '/wrong/wrong2.yml', __DIR__ . '/correct/correct2.yml'];
        yield [__DIR__ . '/wrong/wrong3.yml', __DIR__ . '/correct/correct3.yml'];
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}

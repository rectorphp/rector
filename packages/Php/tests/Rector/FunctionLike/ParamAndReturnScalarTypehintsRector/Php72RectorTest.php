<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Testing\PHPUnit\IntegrationRectorTestCaseTrait;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

/**
 * @covers \Rector\Php\Rector\FunctionLike\ParamScalarTypehintRector
 * @covers \Rector\Php\Rector\FunctionLike\ReturnScalarTypehintRector
 */
final class Php72RectorTest extends AbstractRectorTestCase
{
    use IntegrationRectorTestCaseTrait;

    /**
     * @dataProvider provideIntegrationFiles()
     */
    public function test(string $wrong, string $fixed): void
    {
        $this->doTestFileMatchesExpectedContent($wrong, $fixed);
    }

    public function provideIntegrationFiles(): Iterator
    {
        $integrationFiles = [
            __DIR__ . '/Integration/nikic/object_php72.php.inc',
            __DIR__ . '/Integration/php-cs-fixer-param/php72_object.php.inc',
        ];

        foreach ($integrationFiles as $integrationFile) {
            yield $this->splitContentToOriginalFileAndExpectedFile(new SmartFileInfo($integrationFile));
        }
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config_php72.yml';
    }
}

<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Php\Rector\FunctionLike\ParamTypeDeclarationRector
 * @covers \Rector\Php\Rector\FunctionLike\ReturnTypeDeclarationRector
 */
final class Php72RectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/nikic/object_php72.php.inc',
            __DIR__ . '/Fixture/php-cs-fixer-param/php72_object.php.inc',
        ]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config_php72.yml';
    }
}

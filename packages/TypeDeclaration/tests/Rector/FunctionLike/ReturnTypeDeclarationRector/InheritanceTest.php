<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

final class InheritanceTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/nikic/inheritance.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/nullable_inheritance.php.inc'];
        yield [__DIR__ . '/Fixture/PhpCsFixerReturn/self_static.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/self_parent_static.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/self_inheritance.php.inc'];
    }

    protected function getPhpVersion(): string
    {
        return '7.0';
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }
}

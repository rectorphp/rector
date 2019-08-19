<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

final class CovarianceTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/nikic/inheritance_covariance.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/inheritance_covariance_order.php.inc'];
        yield [__DIR__ . '/Fixture/Covariance/return_interface_to_class.php.inc'];
        yield [__DIR__ . '/Fixture/Covariance/return_nullable_with_parent_interface.php.inc'];
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

<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Iterator;
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

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/nikic/inheritance_covariance.php.inc'];
        yield [__DIR__ . '/Fixture/nikic/inheritance_covariance_order.php.inc'];
        yield [__DIR__ . '/Fixture/Covariance/return_interface_to_class.php.inc'];
        yield [__DIR__ . '/Fixture/Covariance/return_nullable_with_parent_interface.php.inc'];
    }

    protected function getPhpVersion(): string
    {
        // the return type are covariant (flexible) since PHP 7.4 https://wiki.php.net/rfc/covariant-returns-and-contravariant-parameters
        return '7.0';
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }
}

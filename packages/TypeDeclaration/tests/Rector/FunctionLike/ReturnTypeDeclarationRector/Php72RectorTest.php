<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class Php72RectorTest extends AbstractRectorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $parameterProvider = self::$container->get(ParameterProvider::class);
        $parameterProvider->changeParameter('php_version_features', '7.0');
    }

    /**
     * Needed to restore previous version
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $parameterProvider = self::$container->get(ParameterProvider::class);
        $parameterProvider->changeParameter('php_version_features', '10.0');
    }

    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/nikic/object_php72.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }
}

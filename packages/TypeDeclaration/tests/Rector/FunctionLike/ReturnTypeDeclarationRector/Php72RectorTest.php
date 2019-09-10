<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

final class Php72RectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/nikic/object_php72.php.inc']);
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

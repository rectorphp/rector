<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;

final class ReturnTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTests()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTests(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }

    protected function getPhpVersion(): string
    {
        // to prevent union types
        return '7.4';
    }
}

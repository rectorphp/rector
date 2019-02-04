<?php declare(strict_types=1);

namespace Rector\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector;

use Rector\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;

final class AddReturnTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/extended_parent.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return AddReturnTypeDeclarationRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            'Rector\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture\SomeClass' => [
                'parse' => 'array',
                'resolve' => 'SomeType',
                'nullable' => '?SomeType',
                'clear' => '',
            ],
            PHPUnitTestCase::class => [
                'tearDown' => 'void',
            ],
        ];
    }
}

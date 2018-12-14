<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Typehint\ReturnTypehintRector;

use Rector\Rector\Typehint\ReturnTypehintRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReturnTypehintRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ReturnTypehintRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            'Rector\Tests\Rector\Typehint\ReturnTypehintRector\Fixture\SomeClass' => [
                'parse' => 'array',
                'resolve' => 'SomeType',
                'nullable' => '?SomeType',
                'clear' => '',
            ]
        ];
    }
}

<?php declare(strict_types=1);

namespace Rector\Tests\Rector\String_\StringToClassConstantRector;

use Rector\Rector\String_\StringToClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringToClassConstantRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return StringToClassConstantRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            '$stringsToClassConstants' => [
                'compiler.post_dump' => ['Yet\AnotherClass', 'CONSTANT'],
                'compiler.to_class' => ['Yet\AnotherClass', 'class'],
            ],
        ];
    }
}

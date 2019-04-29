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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [StringToClassConstantRector::class => [
            '$stringsToClassConstants' => [
                'compiler.post_dump' => ['Yet\AnotherClass', 'CONSTANT'],
                'compiler.to_class' => ['Yet\AnotherClass', 'class'],
            ],
        ]];
    }
}

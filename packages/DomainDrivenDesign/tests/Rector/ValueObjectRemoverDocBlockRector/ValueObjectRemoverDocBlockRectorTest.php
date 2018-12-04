<?php declare(strict_types=1);

namespace Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector;

use Rector\DomainDrivenDesign\Rector\ValueObjectRemover\ValueObjectRemoverDocBlockRector;
use Rector\DomainDrivenDesign\Tests\Rector\ValueObjectRemoverDocBlockRector\Source\SomeValueObject;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ValueObjectRemoverDocBlockRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Wrong/wrong2.php.inc', __DIR__ . '/Wrong/wrong3.php.inc']
        );
    }

    protected function getRectorClass(): string
    {
        return ValueObjectRemoverDocBlockRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [SomeValueObject::class => 'string'];
    }
}

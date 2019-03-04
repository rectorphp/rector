<?php declare(strict_types=1);

namespace Rector\Tests\Rector\New_\NewToStaticCallRector;

use Rector\Rector\New_\NewToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;
use Rector\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass;

final class NewToStaticCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return NewToStaticCallRector::class;
    }

    /**
     * @return mixed[]|null
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            FromNewClass::class => [IntoStaticClass::class, 'run'],
        ];
    }
}

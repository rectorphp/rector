<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Constant\RenameClassConstantRector;

use Rector\Rector\Constant\RenameClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Constant\RenameClassConstantRector\Source\DifferentClass;
use Rector\Tests\Rector\Constant\RenameClassConstantRector\Source\LocalFormEvents;

final class RenameClassConstantRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RenameClassConstantRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [LocalFormEvents::class => [
            'PRE_BIND' => 'PRE_SUBMIT',
            'BIND' => 'SUBMIT',
            'POST_BIND' => 'POST_SUBMIT',
            'OLD_CONSTANT' => DifferentClass::class . '::NEW_CONSTANT',
        ]];
    }
}

<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Constant\ClassConstantReplacerRector;

use Rector\Rector\Constant\ClassConstantReplacerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Constant\ClassConstantReplacerRector\Source\DifferentClass;
use Rector\Tests\Rector\Constant\ClassConstantReplacerRector\Source\LocalFormEvents;

final class ClassConstantReplacerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ClassConstantReplacerRector::class;
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

<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector;

use Rector\Symfony\Rector\MethodCall\FormTypeInstanceToClassConstRector;
use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\ControllerClass;
use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\FormBuilder;
use Rector\Symfony\Tests\Rector\MethodCall\FormTypeInstanceToClassConstRector\Source\FormType;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FormTypeInstanceToClassConstRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return FormTypeInstanceToClassConstRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            '$controllerClass' => ControllerClass::class,
            '$formBuilderType' => FormBuilder::class,
            '$formType' => FormType::class,
        ];
    }
}

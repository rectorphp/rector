<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;

use Rector\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\Source\NetteServiceDefinition;

final class MethodCallToAnotherMethodCallWithArgumentsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return MethodCallToAnotherMethodCallWithArgumentsRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [NetteServiceDefinition::class => ['setInject' => ['addTag', ['inject']]]];
    }
}

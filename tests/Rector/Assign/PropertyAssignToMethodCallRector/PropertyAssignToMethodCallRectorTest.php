<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector;

use Rector\Rector\Assign\PropertyAssignToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\ChoiceControl;
use Rector\Tests\Rector\Assign\PropertyAssignToMethodCallRector\Source\MultiChoiceControl;

final class PropertyAssignToMethodCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return PropertyAssignToMethodCallRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            ChoiceControl::class => ['checkAllowedValues' => 'checkDefaultValue'],
            MultiChoiceControl::class => ['checkAllowedValues' => 'checkDefaultValue'],
        ];
    }
}

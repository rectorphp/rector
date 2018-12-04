<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangeConstantVisibilityRector;

use Rector\Rector\Visibility\ChangeConstantVisibilityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Visibility\ChangeConstantVisibilityRector\Source\ParentObject;

final class ChangeConstantVisibilityRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ChangeConstantVisibilityRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            ParentObject::class => [
                'TO_BE_PUBLIC_CONSTANT' => 'public',
                'TO_BE_PROTECTED_CONSTANT' => 'protected',
                'TO_BE_PRIVATE_CONSTANT' => 'private',
            ],
            'Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\AnotherClassWithInvalidConstants' => ['TO_BE_PRIVATE_CONSTANT' => 'private'],
        ];
    }
}

<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector;

use Rector\Rector\Visibility\ChangePropertyVisibilityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Source\ParentObject;

final class ChangePropertyVisibilityRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [
                __DIR__ . '/Fixture/fixture.php.inc',
                __DIR__ . '/Fixture/fixture2.php.inc',
                __DIR__ . '/Fixture/fixture3.php.inc',
            ]
        );
    }

    protected function getRectorClass(): string
    {
        return ChangePropertyVisibilityRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            ParentObject::class => [
                'toBePublicProperty' => 'public',
                'toBeProtectedProperty' => 'protected',
                'toBePrivateProperty' => 'private',
                'toBePublicStaticProperty' => 'public',
            ],
            'Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\Wrong\NormalObject' => ['toBePublicStaticProperty' => 'public'],
        ];
    }
}

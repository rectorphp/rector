<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector;

use Rector\Rector\Visibility\ChangeMethodVisibilityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source\ParentObject;

final class ChangeMethodVisibilityRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [ChangeMethodVisibilityRector::class => [
            '$methodToVisibilityByClass' => [
                ParentObject::class => [
                    'toBePublicMethod' => 'public',
                    'toBeProtectedMethod' => 'protected',
                    'toBePrivateMethod' => 'private',
                    'toBePublicStaticMethod' => 'public',
                ],
            ],
        ]];
    }
}

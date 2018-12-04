<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector;

use Rector\Rector\Visibility\ChangeMethodVisibilityRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source\ParentObject;

final class ChangeMethodVisibilityRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Wrong/wrong2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ChangeMethodVisibilityRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [ParentObject::class => [
            'toBePublicMethod' => 'public',
            'toBeProtectedMethod' => 'protected',
            'toBePrivateMethod' => 'private',
            'toBePublicStaticMethod' => 'public',
        ]];
    }
}

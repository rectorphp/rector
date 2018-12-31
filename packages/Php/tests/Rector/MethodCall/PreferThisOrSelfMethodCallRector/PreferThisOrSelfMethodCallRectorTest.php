<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector;

use Rector\Php\Rector\MethodCall\PreferThisOrSelfMethodCallRector;
use Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\AbstractTestCase;
use Rector\Php\Tests\Rector\MethodCall\PreferThisOrSelfMethodCallRector\Source\BeLocalClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PreferThisOrSelfMethodCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/to_self.php.inc', __DIR__ . '/Fixture/to_this.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return PreferThisOrSelfMethodCallRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            AbstractTestCase::class => 'self',
            BeLocalClass::class => 'this',
        ];
    }
}

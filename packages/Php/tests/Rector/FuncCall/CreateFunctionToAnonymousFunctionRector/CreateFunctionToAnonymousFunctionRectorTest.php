<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;

use Rector\Php\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CreateFunctionToAnonymousFunctionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/concat.php.inc',
            __DIR__ . '/Fixture/reference.php.inc',
            __DIR__ . '/Fixture/stackoverflow.php.inc',
            __DIR__ . '/Fixture/drupal.php.inc',
            __DIR__ . '/Fixture/php_net.php.inc',
            __DIR__ . '/Fixture/wordpress.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return CreateFunctionToAnonymousFunctionRector::class;
    }
}

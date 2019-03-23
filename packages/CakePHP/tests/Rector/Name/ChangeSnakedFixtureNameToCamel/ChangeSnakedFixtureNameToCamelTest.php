<?php declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\Name\ChangeSnakedFixtureNameToCamel;

use Rector\CakePHP\Rector\Name\ChangeSnakedFixtureNameToCamelRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeSnakedFixtureNameToCamelTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ChangeSnakedFixtureNameToCamelRector::class;
    }
}

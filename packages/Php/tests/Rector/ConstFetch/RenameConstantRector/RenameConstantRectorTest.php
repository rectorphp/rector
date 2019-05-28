<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\ConstFetch\RenameConstantRector;

use Rector\Php\Rector\ConstFetch\RenameConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameConstantRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/spaghetti.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameConstantRector::class => [
                '$oldToNewConstants' => [
                    'MYSQL_ASSOC' => 'MYSQLI_ASSOC',
                    'OLD_CONSTANT' => 'NEW_CONSTANT',
                ],
            ],
        ];
    }
}

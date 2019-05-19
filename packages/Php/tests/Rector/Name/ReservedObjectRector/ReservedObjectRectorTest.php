<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Name\ReservedObjectRector;

use Rector\Php\Rector\Name\ReservedObjectRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReservedObjectRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/ReservedObject.php',
            __DIR__ . '/Fixture/skip_type_declaration_object.php',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ReservedObjectRector::class => [
                '$reservedKeywordsToReplacements' => [
                    'ReservedObject' => 'SmartObject',
                    'Object' => 'AnotherSmartObject',
                ],
            ],
        ];
    }
}

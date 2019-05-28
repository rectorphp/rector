<?php declare(strict_types=1);

namespace Rector\Tests\Rector\FuncCall\FunctionToNewRector;

use Rector\Rector\FuncCall\FunctionToNewRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionToNewRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FunctionToNewRector::class => [
                '$functionToNew' => [
                    'collection' => ['Collection'],
                ],
            ],
        ];
    }
}

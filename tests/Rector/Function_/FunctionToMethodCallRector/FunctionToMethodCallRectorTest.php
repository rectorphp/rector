<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Function_\FunctionToMethodCallRector;

use Rector\Rector\Function_\FunctionToMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FunctionToMethodCallRectorTest extends AbstractRectorTestCase
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
        return [FunctionToMethodCallRector::class => [
            '$functionToMethodCall' => [
                'view' => ['this', 'render'],
            ],
        ]];
    }
}

<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector;

use Rector\Symfony\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector;
use Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Source\FixtureWebTestCase;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyWebTestCaseAssertionsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/response_code_same.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            SimplifyWebTestCaseAssertionsRector::class => [
                '$webTestCaseClass' => FixtureWebTestCase::class,
            ],
        ];
    }
}

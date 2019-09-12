<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector;

use Rector\NetteToSymfony\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector;
use Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Source\NetteRequest;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FromRequestGetParameterToAttributesGetRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            FromRequestGetParameterToAttributesGetRector::class => [
                '$netteRequestClass' => NetteRequest::class,
            ],
        ];
    }
}

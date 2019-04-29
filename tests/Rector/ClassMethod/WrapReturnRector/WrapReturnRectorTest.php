<?php declare(strict_types=1);

namespace Rector\Tests\Rector\ClassMethod\WrapReturnRector;

use Rector\Rector\ClassMethod\WrapReturnRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\ClassMethod\WrapReturnRector\Source\SomeReturnClass;

final class WrapReturnRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/already_array.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [WrapReturnRector::class => [
            '$typeToMethodToWrap' => [
                SomeReturnClass::class => [
                    'getItem' => 'array',
                ],
            ],
        ]];
    }
}

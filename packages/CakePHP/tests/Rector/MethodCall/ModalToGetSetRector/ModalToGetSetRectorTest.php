<?php declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Source\SomeModelType;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ModalToGetSetRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ModalToGetSetRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [SomeModelType::class => [
            'config' => [
                'get' => 'getConfig',
                'minimal_argument_count' => 2,
                'first_argument_type_to_set' => 'array',
            ],
            'customMethod' => [
                'get' => 'customMethodGetName',
                'set' => 'customMethodSetName',
                'minimal_argument_count' => 2,
                'first_argument_type_to_set' => 'array',
            ],
            'method' => null,
        ]];
    }
}

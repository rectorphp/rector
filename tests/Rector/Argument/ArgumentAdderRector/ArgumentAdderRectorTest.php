<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Argument\ArgumentAdderRector;

use Rector\Rector\Argument\ArgumentAdderRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeContainerBuilder;
use Rector\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeParentClient;

final class ArgumentAdderRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/scoped.php.inc',
            __DIR__ . '/Fixture/already_added.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ArgumentAdderRector::class => [
                '$positionWithDefaultValueByMethodNamesByClassTypes' => [
                    SomeContainerBuilder::class => [
                        'compile' => [
                            0 => [
                                'name' => 'isCompiled',
                                'default_value' => false,
                            ],
                        ],
                        'addCompilerPass' => [
                            2 => [
                                'name' => 'priority',
                                'default_value' => 0,
                                'type' => 'int',
                            ],
                        ],
                    ],

                    // scoped
                    SomeParentClient::class => [
                        'submit' => [
                            2 => [
                                'name' => 'serverParameters',
                                'default_value' => [],
                                'type' => 'array',
                                // scope!
                                'scope' => ['parent_call', 'class_method'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\RenameMethodCallRector;

use Nette\Utils\Html;
use Rector\Rector\MethodCall\RenameMethodCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodCall\RenameMethodCallRector\Source\ClassMethodToBeSkipped;

final class RenameMethodCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameMethodCallRector::class => [
                '$oldToNewMethodsByClass' => [
                    Html::class => [
                        'add' => 'addHtml',
                        'addToArray' => [
                            'name' => 'addHtmlArray',
                            'array_key' => 'hi',
                        ],
                    ],
                    ClassMethodToBeSkipped::class => [
                        'createHtml' => 'testHtml',
                    ],
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector;

use Iterator;
use Nette\Utils\Html;
use Rector\Renaming\Rector\MethodCall\RenameMethodCallRector;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Source\ClassMethodToBeSkipped;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\Source\SomeTranslator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameMethodCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
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
                    SomeTranslator::class => [
                        '__' => 'trans',
                        '__t' => 'trans',
                    ],
                ],
            ],
        ];
    }
}

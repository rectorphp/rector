<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\TranslationBehaviorRector;

use Rector\DoctrineGedmoToKnplabs\Rector\Class_\TranslationBehaviorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TranslationBehaviorRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $fitureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc');
        $this->doTestFileInfo($fitureFileInfo);

        $this->doTestExtraFile('SomeClassTranslation.php', __DIR__ . '/Source/SomeClassTranslation.php');
    }

    protected function getRectorClass(): string
    {
        return TranslationBehaviorRector::class;
    }
}

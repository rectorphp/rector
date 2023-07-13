<?php

declare (strict_types=1);
namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector;

use Iterator;
use RectorPrefix202307\PHPUnit\Framework\Attributes\DataProvider;
use RectorPrefix202307\PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
final class TypedPropertyFromColumnTypeRectorTest extends AbstractRectorTestCase
{
    public function test(string $filePath) : void
    {
        $this->doTestFile($filePath);
    }
    public static function provideData() : Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }
    public function provideConfigFilePath() : string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}

<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver;

use Iterator;
use Rector\CodingStyle\ClassNameImport\ShortNameResolver;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\ValueObject\Application\File;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ShortNameResolverTest extends AbstractTestCase
{
    private ShortNameResolver $shortNameResolver;

    private RectorParser $rectorParser;

    protected function setUp(): void
    {
        $this->boot();
        $this->shortNameResolver = $this->getService(ShortNameResolver::class);
        $this->rectorParser = $this->getService(RectorParser::class);
    }

    /**
     * @dataProvider provideData()
     * @param array<string, class-string<SomeFile>|string> $expectedShortNames
     */
    public function test(string $filePath, array $expectedShortNames): void
    {
        $file = $this->createFileFromFilePath($filePath);
        $shortNames = $this->shortNameResolver->resolveFromFile($file);

        $this->assertSame($expectedShortNames, $shortNames);
    }

    /**
     * @return Iterator<array<int, array<string, string|class-string>>
     */
    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/various_imports.php.inc', [
            'VariousImports' => 'Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Fixture\VariousImports',
            'SomeFile' => 'Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Source\SomeFile',
        ]];

        yield [__DIR__ . '/Fixture/also_aliases.php.inc', [
            'AlsoAliases' => 'Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Fixture\AlsoAliases',
            'AnotherFile' => 'Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Source\SomeFile',
        ]];

        yield [__DIR__ . '/Fixture/partial_names.php.inc', [
            'PartialNames' => 'Rector\Tests\CodingStyle\ClassNameImport\ShortNameResolver\Fixture\PartialNames',
        ]];
    }

    private function createFileFromFilePath(string $filePath): File
    {
        $smartFileInfo = new SmartFileInfo($filePath);
        $file = new File($smartFileInfo, $smartFileInfo->getContents());

        $stmts = $this->rectorParser->parseFile($smartFileInfo);
        $file->hydrateStmtsAndTokens($stmts, $stmts, []);

        return $file;
    }
}

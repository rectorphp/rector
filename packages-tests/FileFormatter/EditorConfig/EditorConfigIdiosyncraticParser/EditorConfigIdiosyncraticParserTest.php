<?php

namespace Rector\Tests\FileFormatter\EditorConfig\EditorConfigIdiosyncraticParser;

use Rector\Core\Contract\EditorConfig\EditorConfigParserInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class EditorConfigIdiosyncraticParserTest extends AbstractTestCase
{
    /**
     * @var EditorConfigParserInterface
     */
    private $editorConfigParser;

    protected function setUp(): void
    {
        $this->boot();
        $this->editorConfigParser = $this->getService(EditorConfigParserInterface::class);
    }

    public function testComposerJsonFile(): void
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withIndentSize(20);
        $editorConfigConfigurationBuilder->withSpace();
        $editorConfigConfigurationBuilder->withLineFeed();
        $editorConfigConfigurationBuilder->withFinalNewline();

        $composerJsonFile = new SmartFileInfo(__DIR__ . '/Fixture/composer.json');
        $editorConfiguration = $this->editorConfigParser->extractConfigurationForFile(
            new File($composerJsonFile, $composerJsonFile->getContents()),
            $editorConfigConfigurationBuilder
        );
        self::assertSame(EditorConfigConfiguration::TAB, $editorConfiguration->getIndentStyle());
        self::assertSame(1, $editorConfiguration->getIndentSize());
    }
}

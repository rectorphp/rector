<?php

namespace Rector\Tests\FileFormatter\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Symplify\PackageBuilder\Configuration\StaticEolConfiguration;

final class EditorConfigConfigurationTest extends TestCase
{
    public function testWithFinalNewline(): void
    {
        $editorConfigConfigurationBuilder = new EditorConfigConfigurationBuilder();
        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        $this->assertSame(StaticEolConfiguration::getEolChar(), $editorConfigConfiguration->getFinalNewline());
    }

    public function testWithoutFinalNewline(): void
    {
        $editorConfigConfigurationBuilder = new EditorConfigConfigurationBuilder();
        $editorConfigConfigurationBuilder->withInsertFinalNewline(false);

        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        $this->assertSame('', $editorConfigConfiguration->getFinalNewline());
    }

    public function testIndentForTab(): void
    {
        $editorConfigConfigurationBuilder = new EditorConfigConfigurationBuilder();
        $editorConfigConfigurationBuilder->withIndent(Indent::createTabWithSize(4));

        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        $this->assertSame('				', $editorConfigConfiguration->getIndent());
    }

    public function testIndentForSpace(): void
    {
        $editorConfigConfigurationBuilder = new EditorConfigConfigurationBuilder();
        $editorConfigConfigurationBuilder->withIndent(Indent::createSpaceWithSize(10));

        $editorConfigConfiguration = $editorConfigConfigurationBuilder->build();

        $this->assertSame('          ', $editorConfigConfiguration->getIndent());
    }
}

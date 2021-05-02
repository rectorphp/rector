<?php

declare(strict_types=1);

namespace Rector\FileFormatter\Formatter;

use Nette\Neon\Neon;
use Rector\Core\Contract\Formatter\FileFormatterInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;

final class NeonFileFormatter implements FileFormatterInterface
{
    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();

        return in_array($smartFileInfo->getExtension(), ['neon'], true);
    }

    public function format(File $file, EditorConfigConfiguration $editorConfigConfiguration): void
    {
        $neon = Neon::encode($file->getFileContent());
        $newFileContent = Neon::decode($neon);

        $file->changeFileContent($newFileContent);
    }

    public function createEditorConfigConfigurationBuilder(): EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withLineFeed();
        $editorConfigConfigurationBuilder->withSpace();
        $editorConfigConfigurationBuilder->withIndentSize(4);
        $editorConfigConfigurationBuilder->withFinalNewline();

        return $editorConfigConfigurationBuilder;
    }
}

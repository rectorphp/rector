<?php

declare(strict_types=1);

namespace Rector\FileFormatter\Formatter;

use Rector\Core\Contract\Formatter\FileFormatterInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Symfony\Component\Yaml\Yaml;

final class YamlFileFormatter implements FileFormatterInterface
{
    public function supports(File $file): bool
    {
        $smartFileInfo = $file->getSmartFileInfo();

        return in_array($smartFileInfo->getExtension(), ['yaml', 'yml'], true);
    }

    public function format(File $file, EditorConfigConfiguration $editorConfigConfiguration): void
    {
        $yaml = Yaml::parse($file->getFileContent());

        $newFileContent = Yaml::dump($yaml, 99, $editorConfigConfiguration->getIndentSize());

        $newFileContent .= $editorConfigConfiguration->getFinalNewline();

        $file->changeFileContent($newFileContent);
    }

    public function createEditorConfigConfigurationBuilder(): EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = EditorConfigConfigurationBuilder::anEditorConfigConfiguration();
        $editorConfigConfigurationBuilder->withLineFeed();
        $editorConfigConfigurationBuilder->withSpace();
        $editorConfigConfigurationBuilder->withoutFinalNewline();
        $editorConfigConfigurationBuilder->withIndentSize(2);

        return $editorConfigConfigurationBuilder;
    }
}

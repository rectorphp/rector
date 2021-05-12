<?php

declare(strict_types=1);

namespace Rector\FileFormatter\Formatter;

use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Contract\Formatter\FileFormatterInterface;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * @see \Rector\Tests\FileFormatter\Formatter\YamlFileFormatter\YamlFileFormatterTest
 */
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

        $file->changeFileContent($newFileContent);
    }

    public function createDefaultEditorConfigConfigurationBuilder(): EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = new EditorConfigConfigurationBuilder();

        $editorConfigConfigurationBuilder->withIndent(Indent::createSpaceWithSize(2));

        return $editorConfigConfigurationBuilder;
    }
}

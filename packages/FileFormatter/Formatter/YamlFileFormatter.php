<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Formatter;

use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Contract\Formatter\FileFormatterInterface;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use RectorPrefix20211231\Symfony\Component\Yaml\Yaml;
/**
 * @see \Rector\Tests\FileFormatter\Formatter\YamlFileFormatter\YamlFileFormatterTest
 */
final class YamlFileFormatter implements \Rector\FileFormatter\Contract\Formatter\FileFormatterInterface
{
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return \in_array($smartFileInfo->getExtension(), ['yaml', 'yml'], \true);
    }
    public function format(\Rector\Core\ValueObject\Application\File $file, \Rector\FileFormatter\ValueObject\EditorConfigConfiguration $editorConfigConfiguration) : void
    {
        $yaml = \RectorPrefix20211231\Symfony\Component\Yaml\Yaml::parse($file->getFileContent(), \RectorPrefix20211231\Symfony\Component\Yaml\Yaml::PARSE_CUSTOM_TAGS);
        $newFileContent = \RectorPrefix20211231\Symfony\Component\Yaml\Yaml::dump($yaml, 99, $editorConfigConfiguration->getIndentSize());
        $file->changeFileContent($newFileContent);
    }
    public function createDefaultEditorConfigConfigurationBuilder() : \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder
    {
        $editorConfigConfigurationBuilder = new \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder();
        $editorConfigConfigurationBuilder->withIndent(\Rector\FileFormatter\ValueObject\Indent::createSpaceWithSize(2));
        return $editorConfigConfigurationBuilder;
    }
}

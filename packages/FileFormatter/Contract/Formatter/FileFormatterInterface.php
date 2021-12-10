<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Contract\Formatter;

use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
interface FileFormatterInterface
{
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool;
    public function format(\Rector\Core\ValueObject\Application\File $file, \Rector\FileFormatter\ValueObject\EditorConfigConfiguration $editorConfigConfiguration) : void;
    public function createDefaultEditorConfigConfigurationBuilder() : \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
}

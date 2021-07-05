<?php

declare (strict_types=1);
namespace Rector\FileFormatter\Contract\Formatter;

use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
interface FileFormatterInterface
{
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     */
    public function supports($file) : bool;
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\FileFormatter\ValueObject\EditorConfigConfiguration $editorConfigConfiguration
     */
    public function format($file, $editorConfigConfiguration) : void;
    public function createDefaultEditorConfigConfigurationBuilder() : \Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
}

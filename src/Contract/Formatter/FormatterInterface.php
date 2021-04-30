<?php

namespace Rector\Core\Contract\Formatter;

use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\EditorConfigConfiguration;
use Rector\Core\ValueObjectFactory\EditorConfigConfigurationBuilder;

interface FormatterInterface
{
    public function supports(File $file): bool;

    public function format(File $file, EditorConfigConfiguration $editorConfigConfiguration): void;

    public function createEditorConfigConfigurationBuilder(): EditorConfigConfigurationBuilder;
}

<?php

declare(strict_types=1);

namespace Rector\FileFormatter;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\Application\File;
use Rector\FileFormatter\Contract\Formatter\FileFormatterInterface;
use Rector\FileFormatter\EditorConfig\EditorConfigParser;
use Rector\FileFormatter\Exception\InvalidNewLineStringException;
use Rector\FileFormatter\Exception\ParseIndentException;
use Rector\FileFormatter\ValueObject\EditorConfigConfiguration;
use Rector\FileFormatter\ValueObject\Indent;
use Rector\FileFormatter\ValueObject\NewLine;
use Rector\FileFormatter\ValueObjectFactory\EditorConfigConfigurationBuilder;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class FileFormatter
{
    /**
     * @param FileFormatterInterface[] $fileFormatters
     */
    public function __construct(
        private EditorConfigParser $editorConfigParser,
        private ParameterProvider $parameterProvider,
        private array $fileFormatters = []
    ) {
    }

    /**
     * @param File[] $files
     */
    public function format(array $files): void
    {
        foreach ($files as $file) {
            if (! $file->hasChanged()) {
                continue;
            }

            foreach ($this->fileFormatters as $fileFormatter) {
                if (! $fileFormatter->supports($file)) {
                    continue;
                }

                $editorConfigConfigurationBuilder = $fileFormatter->createDefaultEditorConfigConfigurationBuilder();

                $this->sniffOriginalFileContent($file, $editorConfigConfigurationBuilder);

                $editorConfiguration = $this->createEditorConfiguration($file, $editorConfigConfigurationBuilder);

                $fileFormatter->format($file, $editorConfiguration);
            }
        }
    }

    private function sniffOriginalFileContent(
        File $file,
        EditorConfigConfigurationBuilder $editorConfigConfigurationBuilder
    ): void {
        // Try to sniff into the original content to get the indentation and new line
        try {
            $indent = Indent::fromContent($file->getOriginalFileContent());
            $editorConfigConfigurationBuilder->withIndent($indent);
        } catch (ParseIndentException) {
        }

        try {
            $newLine = NewLine::fromContent($file->getOriginalFileContent());
            $editorConfigConfigurationBuilder->withNewLine($newLine);
        } catch (InvalidNewLineStringException) {
        }
    }

    private function createEditorConfiguration(
        File $file,
        EditorConfigConfigurationBuilder $editorConfigConfigurationBuilder
    ): EditorConfigConfiguration {
        if (! $this->parameterProvider->provideBoolParameter(Option::ENABLE_EDITORCONFIG)) {
            return $editorConfigConfigurationBuilder->build();
        }

        return $this->editorConfigParser->extractConfigurationForFile($file, $editorConfigConfigurationBuilder);
    }
}

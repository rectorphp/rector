<?php

declare(strict_types=1);

namespace Rector\FileFormatter;

use Rector\Core\Configuration\Option;
use Rector\Core\Contract\EditorConfig\EditorConfigParserInterface;
use Rector\Core\Contract\Formatter\FileFormatterInterface;
use Rector\Core\ValueObject\Application\File;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class FileFormatter
{
    /**
     * @var EditorConfigParserInterface
     */
    private $editorConfigParser;

    /**
     * @var FileFormatterInterface[]
     */
    private $fileFormatters;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @param FileFormatterInterface[] $fileFormatters
     */
    public function __construct(EditorConfigParserInterface $editorConfigParser, ParameterProvider $parameterProvider, array $fileFormatters = [])
    {
        $this->editorConfigParser = $editorConfigParser;
        $this->fileFormatters = $fileFormatters;
        $this->parameterProvider = $parameterProvider;
    }

    /**
     * @param File[] $files
     */
    public function format(array $files): void
    {
        if(!$this->parameterProvider->provideBoolParameter(Option::ENABLE_EDITORCONFIG)) {
            return;
        }

        foreach ($files as $file) {
            if (! $file->hasChanged()) {
                continue;
            }

            foreach ($this->fileFormatters as $fileFormatter) {
                if (! $fileFormatter->supports($file)) {
                    continue;
                }

                $editorConfiguration = $this->editorConfigParser->extractConfigurationForFile(
                    $file,
                    $fileFormatter->createEditorConfigConfigurationBuilder()
                );

                $fileFormatter->format($file, $editorConfiguration);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Printer;

use Migrify\PhpConfigPrinter\Printer\SmartPhpConfigPrinter;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Contract\RectorDefinition\CodeSampleInterface;
use Rector\Core\RectorDefinition\ComposerJsonAwareCodeSample;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DocumentationGenerator\Tests\Printer\CodeSamplePrinter\CodeSamplePrinterTest
 */
final class CodeSamplePrinter
{
    /**
     * @var MarkdownDifferAndFormatter
     */
    private $markdownDifferAndFormatter;

    /**
     * @var SmartPhpConfigPrinter
     */
    private $smartPhpConfigPrinter;

    public function __construct(
        MarkdownDifferAndFormatter $markdownDifferAndFormatter,
        SmartPhpConfigPrinter $smartPhpConfigPrinter
    ) {
        $this->markdownDifferAndFormatter = $markdownDifferAndFormatter;
        $this->smartPhpConfigPrinter = $smartPhpConfigPrinter;
    }

    public function printCodeSamples(RectorDefinition $rectorDefinition, RectorInterface $rector): string
    {
        $content = '';

        foreach ($rectorDefinition->getCodeSamples() as $codeSample) {
            $content .= $this->printConfiguration($rector, $codeSample);
            $content .= $this->printCodeSample($codeSample);
        }

        return $content;
    }

    private function printConfiguration(RectorInterface $rector, CodeSampleInterface $codeSample): string
    {
        if (! $codeSample instanceof ConfiguredCodeSample) {
            return '';
        }

        $configuration = [
            get_class($rector) => $codeSample->getConfiguration(),
        ];

        $phpConfigContent = $this->smartPhpConfigPrinter->printConfiguredServices($configuration);
        $wrappedPhpConfigContent = $this->printCodeWrapped($phpConfigContent, 'php');

        return $wrappedPhpConfigContent . PHP_EOL . '↓' . PHP_EOL . PHP_EOL;
    }

    private function printCodeSample(CodeSampleInterface $codeSample): string
    {
        $diff = $this->markdownDifferAndFormatter->bareDiffAndFormatWithoutColors(
            $codeSample->getCodeBefore(),
            $codeSample->getCodeAfter()
        );

        $content = $this->printCodeWrapped($diff, 'diff');

        $extraFileContent = $codeSample->getExtraFileContent();
        if ($extraFileContent !== null) {
            $content .= PHP_EOL . '**New file**' . PHP_EOL;
            $content .= $this->printCodeWrapped($extraFileContent, 'php');
        }

        return $content . $this->printComposerJsonAwareCodeSample($codeSample);
    }

    private function printCodeWrapped(string $content, string $format): string
    {
        $message = sprintf('```%s%s%s%s```', $format, PHP_EOL, rtrim($content), PHP_EOL);
        return $message . PHP_EOL;
    }

    private function printComposerJsonAwareCodeSample(CodeSampleInterface $codeSample): string
    {
        if (! $codeSample instanceof ComposerJsonAwareCodeSample) {
            return '';
        }

        $composerJsonContent = $codeSample->getComposerJsonContent();

        return PHP_EOL . 'composer.json' . PHP_EOL . $this->printCodeWrapped($composerJsonContent, 'json') . PHP_EOL;
    }
}

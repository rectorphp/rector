<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\OutputFormatter;

use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Contract\RectorDefinition\CodeSampleInterface;
use Rector\Core\RectorDefinition\ComposerJsonAwareCodeSample;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\SymfonyPhpConfig\Printer\ReturnClosurePrinter;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CodeSamplePrinter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MarkdownDifferAndFormatter
     */
    private $markdownDifferAndFormatter;

    /**
     * @var ReturnClosurePrinter
     */
    private $returnClosurePrinter;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        MarkdownDifferAndFormatter $markdownDifferAndFormatter,
        ReturnClosurePrinter $returnClosurePrinter
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->markdownDifferAndFormatter = $markdownDifferAndFormatter;
        $this->returnClosurePrinter = $returnClosurePrinter;
    }

    public function printCodeSamples(RectorDefinition $rectorDefinition, RectorInterface $rector): void
    {
        foreach ($rectorDefinition->getCodeSamples() as $codeSample) {
            $this->symfonyStyle->newLine();

            $this->printConfiguration($rector, $codeSample);
            $this->printCodeSample($codeSample);
        }
    }

    private function printConfiguration(RectorInterface $rector, CodeSampleInterface $codeSample): void
    {
        if (! $codeSample instanceof ConfiguredCodeSample) {
            return;
        }

        $configuration = [
            get_class($rector) => $codeSample->getConfiguration(),
        ];

        $phpConfigContent = $this->returnClosurePrinter->printServices($configuration);
        $this->printCodeWrapped($phpConfigContent, 'php');

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('↓');
        $this->symfonyStyle->newLine();
    }

    private function printCodeSample(CodeSampleInterface $codeSample): void
    {
        $diff = $this->markdownDifferAndFormatter->bareDiffAndFormatWithoutColors(
            $codeSample->getCodeBefore(),
            $codeSample->getCodeAfter()
        );

        $this->printCodeWrapped($diff, 'diff');

        $extraFileContent = $codeSample->getExtraFileContent();
        if ($extraFileContent !== null) {
            $this->symfonyStyle->newLine();
            $this->symfonyStyle->writeln('**New file**');
            $this->symfonyStyle->newLine();
            $this->printCodeWrapped($extraFileContent, 'php');
        }

        $this->printComposerJsonAwareCodeSample($codeSample);
    }

    private function printCodeWrapped(string $content, string $format): void
    {
        $message = sprintf('```%s%s%s%s```', $format, PHP_EOL, rtrim($content), PHP_EOL);
        $this->symfonyStyle->writeln($message);
    }

    private function printComposerJsonAwareCodeSample(CodeSampleInterface $codeSample): void
    {
        if (! $codeSample instanceof ComposerJsonAwareCodeSample) {
            return;
        }

        $composerJsonContent = $codeSample->getComposerJsonContent();
        $this->symfonyStyle->newLine(1);
        $this->symfonyStyle->writeln('`composer.json`');
        $this->symfonyStyle->newLine(1);
        $this->printCodeWrapped($composerJsonContent, 'json');
        $this->symfonyStyle->newLine();
    }
}

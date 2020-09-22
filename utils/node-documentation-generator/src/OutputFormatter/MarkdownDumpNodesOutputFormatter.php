<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\OutputFormatter;

use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeCodeSample;
use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeInfo;
use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeInfos;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MarkdownDumpNodesOutputFormatter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function format(NodeInfos $nodeInfos): void
    {
        $this->symfonyStyle->writeln('# Node Overview');
        $this->symfonyStyle->newLine();

        $this->symfonyStyle->newLine();

        foreach ($nodeInfos->provide() as $nodeInfo) {
            $message = sprintf('## `%s`', $nodeInfo->getClass());
            $this->symfonyStyle->writeln($message);
            $this->symfonyStyle->newLine();

            $this->printCodeExample($nodeInfo);
            $this->printPublicProperties($nodeInfo);

            $this->symfonyStyle->writeln('<br>');
            $this->symfonyStyle->newLine();
        }
    }

    private function printCodeExample(NodeInfo $nodeInfo): void
    {
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('### Example PHP Code');
        $this->symfonyStyle->newLine();

        foreach ($nodeInfo->getNodeCodeSamples() as $nodeCodeSample) {
            $this->printNodeCodeSample($nodeCodeSample);
            $this->symfonyStyle->newLine();
        }
    }

    private function printPublicProperties(NodeInfo $nodeInfo): void
    {
        if (! $nodeInfo->hasPublicProperties()) {
            return;
        }

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('### Public Properties');
        $this->symfonyStyle->newLine();

        foreach ($nodeInfo->getPublicPropertyInfos() as $publicPropertyInfo) {
            $this->symfonyStyle->writeln($publicPropertyInfo);
        }
    }

    private function printNodeCodeSample(NodeCodeSample $nodeCodeSample): void
    {
        $this->printPhpSnippet($nodeCodeSample->getPhpCode());

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('↓');
        $this->symfonyStyle->newLine();

        $this->printPhpSnippet($nodeCodeSample->getPrintedContent());
    }

    private function printPhpSnippet(string $printedContent): void
    {
        $message = sprintf('```php%s%s%s```', PHP_EOL, $printedContent, PHP_EOL);
        $this->symfonyStyle->writeln($message);
    }
}

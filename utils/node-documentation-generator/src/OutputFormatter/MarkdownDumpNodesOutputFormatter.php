<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\OutputFormatter;

use Nette\Utils\Strings;
use Rector\Utils\NodeDocumentationGenerator\Node\NodeInfoCollector;
use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeCodeSample;
use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeInfo;
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

    public function format(NodeInfoCollector $nodeInfoCollector): void
    {
        $this->symfonyStyle->writeln('# Node Overview');
        $this->symfonyStyle->newLine();

        $this->printCategories($nodeInfoCollector);

        $this->symfonyStyle->newLine();

        /** @var string $category */
        foreach ($nodeInfoCollector->getNodeInfosByCategory() as $category => $nodeInfos) {
            $categoryTitle = $this->createCategoryTitle($category);
            $this->symfonyStyle->writeln('## ' . $categoryTitle);
            $this->symfonyStyle->newLine();

            foreach ($nodeInfos as $nodeInfo) {
                $message = sprintf('### `%s`', $nodeInfo->getClass());
                $this->symfonyStyle->writeln($message);
                $this->symfonyStyle->newLine();

                $this->printRequiredArguments($nodeInfo);
                $this->printCodeExample($nodeInfo);
                $this->printPublicProperties($nodeInfo);

                $this->symfonyStyle->writeln('<br>');
                $this->symfonyStyle->newLine();
            }
        }
    }

    private function printCategories(NodeInfoCollector $nodeInfoCollector): void
    {
        foreach ($nodeInfoCollector->getCategories() as $category) {
            $categoryTitle = $this->createCategoryTitle($category);
            $categoryLink = sprintf('* [%s](#%s)', $categoryTitle, Strings::webalize($categoryTitle));

            $this->symfonyStyle->writeln($categoryLink);
        }
    }

    private function createCategoryTitle(string $category): string
    {
        if (class_exists($category)) {
            return sprintf('Children of "%s"', $category);
        }

        return $category;
    }

    private function printRequiredArguments(NodeInfo $nodeInfo): void
    {
        if (! $nodeInfo->hasRequiredArguments()) {
            return;
        }

        $this->symfonyStyle->writeln(' * requires arguments on construct');
        $this->symfonyStyle->newLine();
    }

    private function printCodeExample(NodeInfo $nodeInfo): void
    {
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('#### Example PHP Code');
        $this->symfonyStyle->newLine();

        foreach ($nodeInfo->getCodeSamples() as $printedSample) {
            $this->printPhpSnippet($printedSample);
        }

        foreach ($nodeInfo->getNodeCodeSamples() as $nodeCodeSample) {
            $this->printNodeCodeSample($nodeCodeSample);
        }
    }

    private function printPublicProperties(NodeInfo $nodeInfo): void
    {
        if (! $nodeInfo->hasPublicProperties()) {
            return;
        }

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('#### Public Properties');
        $this->symfonyStyle->newLine();

        foreach ($nodeInfo->getPublicPropertyInfos() as $publicPropertyInfo) {
            $this->symfonyStyle->writeln($publicPropertyInfo);
        }
    }

    private function printPhpSnippet(string $printedContent): void
    {
        $message = sprintf('```php%s%s%s```', PHP_EOL, $printedContent, PHP_EOL);
        $this->symfonyStyle->writeln($message);
    }

    private function printNodeCodeSample(NodeCodeSample $nodeCodeSample): void
    {
        $message = sprintf('```php%s%s%s```', PHP_EOL, $nodeCodeSample->getPhpCode(), PHP_EOL);
        $this->symfonyStyle->writeln($message);

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('↓');
        $this->symfonyStyle->newLine();

        $message = sprintf('```php%s%s%s```', PHP_EOL, $nodeCodeSample->getPrintedContent(), PHP_EOL);
        $this->symfonyStyle->writeln($message);
    }
}

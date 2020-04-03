<?php

declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\OutputFormatter\DumpNodes;

use Nette\Utils\Strings;
use Rector\Utils\DocumentationGenerator\Node\NodeInfoResult;
use Rector\Utils\DocumentationGenerator\ValueObject\NodeInfo;
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

    public function format(NodeInfoResult $nodeInfoResult): void
    {
        $this->symfonyStyle->writeln('# Node Overview');
        $this->symfonyStyle->newLine();

        $this->printCategories($nodeInfoResult);

        $this->symfonyStyle->newLine();

        /** @var string $category */
        foreach ($nodeInfoResult->getNodeInfos() as $category => $nodeInfos) {
            $categoryTitle = $this->createCategoryTitle($category);
            $this->symfonyStyle->writeln('## ' . $categoryTitle);
            $this->symfonyStyle->newLine();

            foreach ($nodeInfos as $nodeInfo) {
                $this->symfonyStyle->writeln(sprintf('### `%s`', $nodeInfo->getClass()));
                $this->symfonyStyle->newLine();

                $this->printRequiredArguments($nodeInfo);
                $this->printCodeExample($nodeInfo);
                $this->printPublicProperties($nodeInfo);

                $this->symfonyStyle->writeln('<br>');
                $this->symfonyStyle->newLine();
            }
        }
    }

    private function printCategories(NodeInfoResult $nodeInfoResult): void
    {
        foreach ($nodeInfoResult->getCategories() as $category) {
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
        $this->symfonyStyle->writeln(sprintf('```php%s%s%s```', PHP_EOL, $nodeInfo->getPrintedContent(), PHP_EOL));
    }
}

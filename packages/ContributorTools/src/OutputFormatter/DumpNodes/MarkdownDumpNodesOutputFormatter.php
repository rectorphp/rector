<?php declare(strict_types=1);

namespace Rector\ContributorTools\OutputFormatter\DumpNodes;

use Rector\ContributorTools\Contract\OutputFormatter\DumpNodesOutputFormatterInterface;
use Rector\ContributorTools\Node\NodeInfo;
use Rector\ContributorTools\Node\NodeInfoResult;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MarkdownDumpNodesOutputFormatter implements DumpNodesOutputFormatterInterface
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function getName(): string
    {
        return 'markdown';
    }

    public function format(NodeInfoResult $nodeInfoResult): void
    {
        $this->symfonyStyle->writeln('# Node Overview');
        $this->symfonyStyle->newLine();

        /** @var NodeInfo[] $nodeInfos */
        foreach ($nodeInfoResult->getNodeInfos() as $category => $nodeInfos) {
            if (class_exists($category)) {
                $this->symfonyStyle->writeln('## ' . sprintf('Children of "%s"', $category));
            } else {
                $this->symfonyStyle->writeln('## ' . $category);
            }
            $this->symfonyStyle->newLine();

            foreach ($nodeInfos as $nodeInfo) {
                $this->symfonyStyle->writeln(sprintf('#### `%s`', $nodeInfo->getClass()));
                $this->symfonyStyle->newLine();
                $this->symfonyStyle->writeln(
                    sprintf('```php%s%s%s```', PHP_EOL, $nodeInfo->getPrintedContent(), PHP_EOL)
                );

                $this->symfonyStyle->writeln('<br>');
                $this->symfonyStyle->newLine();
            }
        }
    }
}

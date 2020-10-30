<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\Command;

use Nette\Loaders\RobotLoader;
use Rector\Core\Console\Command\AbstractCommand;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Testing\Finder\RectorsFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class NodeTypesStatisticsCommand extends AbstractCommand
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[DOCS] Show statistics of used and unused node types in PHP Rector');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $phpRectors = $this->resolvePhpRectors();
        $nodeTypes = $this->collectNodeTypesFromGetNodeTypes($phpRectors);

        $nodeTypesCount = $this->resolveNodeTypesByCount($nodeTypes);
        $this->printMostUsedNodeTypesTable($nodeTypesCount);

        $uniqueNodeTypes = array_unique($nodeTypes);
        $uniqueNodeTypesCount = count($uniqueNodeTypes);
        $message = sprintf(
            'In total, %d Rectors listens to %d node types - with only %d unique types',
            count($phpRectors),
            count($nodeTypes),
            $uniqueNodeTypesCount
        );

        $this->symfonyStyle->success($message);

        $robotLoader= new RobotLoader();
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/nodes');
        $robotLoader->addDirectory(__DIR__ . '/../../../../vendor/nikic/php-parser/lib/PhpParser/Node');
        $robotLoader->rebuild();

        $allNodeTypes = array_keys($robotLoader->getIndexedClasses());
        $unusedNodeTypes = array_diff($allNodeTypes, $uniqueNodeTypes);

        // explicitly,
        $this->symfonyStyle->listing($unusedNodeTypes);
        // @todo - print nodes that are not used at all

        return ShellCode::SUCCESS;
    }

    /**
     * @param string[] $nodeTypes
     * @return array<string, int>
     */
    private function resolveNodeTypesByCount(array $nodeTypes): array
    {
        $nodeTypesWithCount = array_count_values($nodeTypes);
        arsort($nodeTypesWithCount);

        return $nodeTypesWithCount;
    }

    /**
     * @param PhpRectorInterface[] $phpRectors
     * @return string[]
     */
    private function collectNodeTypesFromGetNodeTypes(array $phpRectors): array
    {
        $nodeTypes = [];
        foreach ($phpRectors as $phpRector) {
            foreach ($phpRector->getNodeTypes() as $nodeType) {
                $nodeTypes[] = $nodeType;
            }
        }
        return $nodeTypes;
    }

    /**
     * @return PhpRectorInterface[]
     */
    private function resolvePhpRectors(): array
    {
        $rectorsFinder = new RectorsFinder();
        return $rectorsFinder->findAndCreatePhpRectors();
    }

    /**
     * @param array<string, int> $nodeTypesCount
     */
    private function printMostUsedNodeTypesTable(array $nodeTypesCount): void
    {
        $rows = $this->createTableRows($nodeTypesCount);
        $this->symfonyStyle->table(['#', 'Node Type', 'Rector Count'], $rows);
        $this->symfonyStyle->newLine();
    }

    /**
     * @param array<string, int> $nodeTypesCount
     * @return array<array<string|int>>
     */
    private function createTableRows(array $nodeTypesCount): array
    {
        $rows = [];
        $i = 1;
        foreach ($nodeTypesCount as $nodeType => $count) {
            $rows[] = [$i, $nodeType, $count];
            ++$i;
        }
        return $rows;
    }
}

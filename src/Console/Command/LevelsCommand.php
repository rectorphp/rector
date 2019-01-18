<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\Strings;
use Rector\Console\Shell;
use function Safe\sort;
use function Safe\sprintf;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class LevelsCommand extends AbstractCommand
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
        $this->setDescription('List available levels');
        $this->addArgument('name', InputArgument::OPTIONAL, 'Filter levels by provded name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $levels = $this->getAvailbleLevels();

        if ($input->getArgument('name')) {
            $levels = $this->filterLevelsByName($input, $levels);
        }

        $this->symfonyStyle->title(sprintf('%d available levels:', count($levels)));
        $this->symfonyStyle->listing($levels);

        return Shell::CODE_SUCCESS;
    }

    /**
     * @return string[]
     */
    private function getAvailbleLevels(): array
    {
        $finder = Finder::create()->files()
            ->in(__DIR__ . '/../../../config/level');

        $levels = [];
        foreach ($finder->getIterator() as $fileInfo) {
            $levels[] = $fileInfo->getBasename('.' . $fileInfo->getExtension());
        }

        sort($levels);

        return array_unique($levels);
    }

    /**
     * @param string[] $levels
     * @return string[]
     */
    private function filterLevelsByName(InputInterface $input, array $levels): array
    {
        $name = $input->getArgument('name');

        return array_filter($levels, function (string $level) use ($name): bool {
            return (bool) Strings::match($level, sprintf('#%s#', $name));
        });
    }
}

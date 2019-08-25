<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\Strings;
use Rector\Console\Shell;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class SetsCommand extends AbstractCommand
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
        $this->setDescription('List available sets');
        $this->addArgument('name', InputArgument::OPTIONAL, 'Filter sets by provided name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sets = $this->getAvailbleSets();

        if ($input->getArgument('name')) {
            $sets = $this->filterSetsByName($input, $sets);
        }

        $this->symfonyStyle->title(sprintf('%d available sets:', count($sets)));
        $this->symfonyStyle->listing($sets);

        return Shell::CODE_SUCCESS;
    }

    /**
     * @return string[]
     */
    private function getAvailbleSets(): array
    {
        $finder = Finder::create()->files()
            ->in(__DIR__ . '/../../../config/set');

        $sets = [];
        foreach ($finder->getIterator() as $fileInfo) {
            $sets[] = $fileInfo->getBasename('.' . $fileInfo->getExtension());
        }

        sort($sets);

        return array_unique($sets);
    }

    /**
     * @param string[] $sets
     * @return string[]
     */
    private function filterSetsByName(InputInterface $input, array $sets): array
    {
        $name = (string) $input->getArgument('name');

        return array_filter($sets, function (string $set) use ($name): bool {
            return (bool) Strings::match($set, sprintf('#%s#', $name));
        });
    }
}

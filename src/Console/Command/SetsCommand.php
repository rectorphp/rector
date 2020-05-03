<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Nette\Utils\Strings;
use Rector\Core\Set\SetProvider;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;

final class SetsCommand extends AbstractCommand
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var SetProvider
     */
    private $setProvider;

    public function __construct(SymfonyStyle $symfonyStyle, SetProvider $setProvider)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->setProvider = $setProvider;

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
        $sets = $this->setProvider->provide();

        $name = (string) $input->getArgument('name');
        if ($name) {
            $sets = $this->filterSetsByName($name, $sets);
        }
        $this->symfonyStyle->title(sprintf('%d available sets:', count($sets)));
        $this->symfonyStyle->listing($sets);

        if ($name === '') {
            $this->symfonyStyle->note('Tip: filter sets by name, e.g. "vendor/bin/rector sets symfony"');
        }

        return ShellCode::SUCCESS;
    }

    /**
     * @param string[] $sets
     * @return string[]
     */
    private function filterSetsByName(string $name, array $sets): array
    {
        return array_filter($sets, function (string $set) use ($name): bool {
            return (bool) Strings::match($set, sprintf('#%s#', $name));
        });
    }
}

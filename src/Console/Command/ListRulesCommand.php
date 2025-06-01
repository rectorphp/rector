<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202506\Nette\Utils\Json;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Configuration\ConfigurationRuleFilter;
use Rector\Configuration\OnlyRuleResolver;
use Rector\Configuration\Option;
use Rector\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use RectorPrefix202506\Symfony\Component\Console\Command\Command;
use RectorPrefix202506\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202506\Symfony\Component\Console\Input\InputOption;
use RectorPrefix202506\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202506\Symfony\Component\Console\Style\SymfonyStyle;
final class ListRulesCommand extends Command
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    /**
     * @readonly
     */
    private SkippedClassResolver $skippedClassResolver;
    /**
     * @readonly
     */
    private OnlyRuleResolver $onlyRuleResolver;
    /**
     * @readonly
     */
    private ConfigurationRuleFilter $configurationRuleFilter;
    /**
     * @var RectorInterface[]
     * @readonly
     */
    private array $rectors;
    /**
     * @param RectorInterface[] $rectors
     */
    public function __construct(SymfonyStyle $symfonyStyle, SkippedClassResolver $skippedClassResolver, OnlyRuleResolver $onlyRuleResolver, ConfigurationRuleFilter $configurationRuleFilter, array $rectors)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->skippedClassResolver = $skippedClassResolver;
        $this->onlyRuleResolver = $onlyRuleResolver;
        $this->configurationRuleFilter = $configurationRuleFilter;
        $this->rectors = $rectors;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('list-rules');
        $this->setDescription('Show loaded Rectors');
        $this->setAliases(['show-rules']);
        $this->addOption(Option::OUTPUT_FORMAT, null, InputOption::VALUE_REQUIRED, 'Select output format', ConsoleOutputFormatter::NAME);
        $this->addOption(Option::ONLY, null, InputOption::VALUE_REQUIRED, 'Fully qualified rule class name');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $onlyRule = $input->getOption(Option::ONLY);
        if ($onlyRule !== null) {
            $onlyRule = $this->onlyRuleResolver->resolve($onlyRule);
        }
        $rectorClasses = $this->resolveRectorClasses($onlyRule);
        $skippedClasses = $this->getSkippedCheckers();
        $outputFormat = $input->getOption(Option::OUTPUT_FORMAT);
        if ($outputFormat === 'json') {
            $data = ['rectors' => $rectorClasses, 'skipped-rectors' => $skippedClasses];
            echo Json::encode($data, \true) . \PHP_EOL;
            return Command::SUCCESS;
        }
        $this->symfonyStyle->title('Loaded Rector rules');
        $this->symfonyStyle->listing($rectorClasses);
        if ($skippedClasses !== []) {
            $this->symfonyStyle->title('Skipped Rector rules');
            $this->symfonyStyle->listing($skippedClasses);
        }
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->note(\sprintf('Loaded %d rules', \count($rectorClasses)));
        return Command::SUCCESS;
    }
    /**
     * @return array<class-string<RectorInterface>>
     */
    private function resolveRectorClasses(?string $onlyRule) : array
    {
        $customRectors = \array_filter($this->rectors, static fn(RectorInterface $rector): bool => !$rector instanceof PostRectorInterface);
        if ($onlyRule !== null) {
            $customRectors = $this->configurationRuleFilter->filterOnlyRule($customRectors, $onlyRule);
        }
        $rectorClasses = \array_map(static fn(RectorInterface $rector): string => \get_class($rector), $customRectors);
        \sort($rectorClasses);
        return \array_unique($rectorClasses);
    }
    /**
     * @return string[]
     */
    private function getSkippedCheckers() : array
    {
        $skippedCheckers = [];
        foreach ($this->skippedClassResolver->resolve() as $checkerClass => $fileList) {
            // ignore specific skips
            if ($fileList !== null) {
                continue;
            }
            $skippedCheckers[] = $checkerClass;
        }
        return $skippedCheckers;
    }
}

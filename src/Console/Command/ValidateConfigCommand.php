<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202609\Nette\Utils\Json;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\ChangesReporting\Output\JsonOutputFormatter;
use Rector\Configuration\Option;
use Rector\Console\ExitCode;
use Rector\Reporting\DeprecatedRulesReporter;
use Rector\Reporting\MissConfigurationReporter;
use Rector\Skipper\SkipCriteriaResolver\SkippedClassResolver;
use Rector\ValueObject\Configuration;
use RectorPrefix202609\Symfony\Component\Console\Command\Command;
use RectorPrefix202609\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202609\Symfony\Component\Console\Input\InputOption;
use RectorPrefix202609\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202609\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @see \Rector\Tests\Console\Command\ValidateConfigCommandTest
 */
final class ValidateConfigCommand extends Command
{
    /**
     * @readonly
     */
    private SymfonyStyle $symfonyStyle;
    /**
     * @readonly
     */
    private DeprecatedRulesReporter $deprecatedRulesReporter;
    /**
     * @readonly
     */
    private MissConfigurationReporter $missConfigurationReporter;
    /**
     * @readonly
     */
    private SkippedClassResolver $skippedClassResolver;
    public function __construct(SymfonyStyle $symfonyStyle, DeprecatedRulesReporter $deprecatedRulesReporter, MissConfigurationReporter $missConfigurationReporter, SkippedClassResolver $skippedClassResolver)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->deprecatedRulesReporter = $deprecatedRulesReporter;
        $this->missConfigurationReporter = $missConfigurationReporter;
        $this->skippedClassResolver = $skippedClassResolver;
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setName('validate-config');
        $this->setDescription('Report config hygiene issues without processing any files');
        $this->addOption(Option::OUTPUT_FORMAT, null, InputOption::VALUE_REQUIRED, sprintf('Output format: "%s" or "%s"', ConsoleOutputFormatter::NAME, JsonOutputFormatter::NAME), ConsoleOutputFormatter::NAME);
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isJsonOutput = $input->getOption(Option::OUTPUT_FORMAT) === JsonOutputFormatter::NAME;
        // silence the human-readable warnings, so only the JSON payload lands on stdout
        if ($isJsonOutput) {
            $this->symfonyStyle->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }
        $issueCount = 0;
        $issueCount += $this->deprecatedRulesReporter->reportDeprecatedRules();
        $issueCount += $this->deprecatedRulesReporter->reportDeprecatedSkippedRules();
        $issueCount += $this->deprecatedRulesReporter->reportDeprecatedCacheMetaExtensions();
        $issueCount += $this->deprecatedRulesReporter->reportDeprecatedPhpSetsMethods();
        $issueCount += $this->deprecatedRulesReporter->reportDeprecatedAttributesSetsArgs();
        $issueCount += $this->deprecatedRulesReporter->reportDeprecatedComposerBasedArgs();
        $issueCount += $this->deprecatedRulesReporter->reportDeprecatedRectorUnsupportedMethods();
        $issueCount += $this->missConfigurationReporter->reportSkippedNeverRegisteredRules();
        $issueCount += $this->missConfigurationReporter->reportSkippedNonRectorClasses();
        $issueCount += $this->reportDeprecatedSkippedClasses();
        $issueCount += $this->reportSetAndRulesDuplicatedRegistrations();
        if ($isJsonOutput) {
            echo Json::encode(['valid' => $issueCount === 0, 'issue_count' => $issueCount], \true) . \PHP_EOL;
            return $issueCount === 0 ? ExitCode::SUCCESS : ExitCode::FAILURE;
        }
        if ($issueCount === 0) {
            $this->symfonyStyle->success('Config is valid, no issues found');
            return ExitCode::SUCCESS;
        }
        $this->symfonyStyle->error(sprintf('%d config %s found, see the warnings above', $issueCount, $issueCount === 1 ? 'issue' : 'issues'));
        return ExitCode::FAILURE;
    }
    private function reportDeprecatedSkippedClasses(): int
    {
        $deprecatedSkippedClasses = $this->skippedClassResolver->resolveDeprecatedSkippedClasses();
        if ($deprecatedSkippedClasses === []) {
            return 0;
        }
        $this->symfonyStyle->warning(sprintf('These rules are skipped, but are deprecated. Most likely you do not need to skip them anymore, remove them: %s%s', "\n\n", '* ' . implode("\n* ", $deprecatedSkippedClasses) . "\n"));
        return count($deprecatedSkippedClasses);
    }
    private function reportSetAndRulesDuplicatedRegistrations(): int
    {
        $setAndRulesDuplicatedRegistrations = (new Configuration())->getBothSetAndRulesDuplicatedRegistrations();
        if ($setAndRulesDuplicatedRegistrations === []) {
            return 0;
        }
        $this->symfonyStyle->warning(sprintf('These rules are registered in both sets and "withRules()". Remove them from "withRules()" to avoid duplications: %s* %s', "\n\n", implode(' * ', $setAndRulesDuplicatedRegistrations) . "\n"));
        return count($setAndRulesDuplicatedRegistrations);
    }
}

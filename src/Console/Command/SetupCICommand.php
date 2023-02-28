<?php

declare (strict_types=1);
namespace Rector\Core\Console\Command;

use RectorPrefix202302\Nette\Utils\FileSystem;
use RectorPrefix202302\Nette\Utils\Strings;
use RectorPrefix202302\OndraM\CiDetector\CiDetector;
use RectorPrefix202302\Symfony\Component\Console\Command\Command;
use RectorPrefix202302\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202302\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202302\Symfony\Component\Console\Style\SymfonyStyle;
use RectorPrefix202302\Symfony\Component\Process\Process;
final class SetupCICommand extends Command
{
    /**
     * @var string
     * @see https://regex101.com/r/etcmog/1
     */
    private const GITHUB_REPOSITORY_REGEX = '#github\\.com:(?<repository_name>.*?)\\.git#';
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('setup-ci');
        $this->setDescription('Add CI workflow to let Rector work for you');
    }
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // detect current CI
        $ci = $this->resolveCurrentCI();
        if ($ci === null) {
            $this->symfonyStyle->error('No CI detected');
            return self::FAILURE;
        }
        if ($ci === CiDetector::CI_GITHUB_ACTIONS) {
            $rectorWorkflowFilePath = \getcwd() . '/.github/workflows/rector.yaml';
            if (\file_exists($rectorWorkflowFilePath)) {
                $this->symfonyStyle->warning('The "rector.yaml" workflow already exists');
                return self::SUCCESS;
            }
            $currentRepository = $this->resolveCurrentRepositoryName(\getcwd());
            if ($currentRepository === null) {
                $this->symfonyStyle->error('Current repository name could not be resolved');
                return self::FAILURE;
            }
            $workflowTemplate = FileSystem::read(__DIR__ . '/../../../templates/rector-github-action-check.yaml');
            $workflowContents = \strtr($workflowTemplate, ['__CURRENT_REPOSITORY__' => $currentRepository]);
            FileSystem::write($rectorWorkflowFilePath, $workflowContents);
            $this->symfonyStyle->success('The "rector.yaml" workflow was added');
        }
        return Command::SUCCESS;
    }
    /**
     * @return CiDetector::CI_*|null
     */
    private function resolveCurrentCI() : ?string
    {
        if (\file_exists(\getcwd() . '/.github')) {
            return CiDetector::CI_GITHUB_ACTIONS;
        }
        return null;
    }
    private function resolveCurrentRepositoryName(string $currentDirectory) : ?string
    {
        // resolve current repository name
        $process = new Process(['git', 'remote', 'get-url', 'origin'], $currentDirectory, null, null, null);
        $process->run();
        $output = $process->getOutput();
        $match = Strings::match($output, self::GITHUB_REPOSITORY_REGEX);
        return $match['repository_name'] ?? null;
    }
}

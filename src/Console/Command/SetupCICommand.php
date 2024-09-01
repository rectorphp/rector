<?php

declare (strict_types=1);
namespace Rector\Console\Command;

use RectorPrefix202409\Nette\Utils\FileSystem;
use RectorPrefix202409\OndraM\CiDetector\CiDetector;
use Rector\Git\RepositoryHelper;
use RectorPrefix202409\Symfony\Component\Console\Command\Command;
use RectorPrefix202409\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix202409\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix202409\Symfony\Component\Console\Style\SymfonyStyle;
use function sprintf;
final class SetupCICommand extends Command
{
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
        if ($ci === CiDetector::CI_GITLAB) {
            return $this->handleGitlabCi();
        }
        if ($ci === CiDetector::CI_GITHUB_ACTIONS) {
            return $this->handleGithubActions();
        }
        $noteMessage = sprintf('Only Github and GitLab are currently supported.%s Contribute your CI template to Rector to make this work: %s', \PHP_EOL, 'https://github.com/rectorphp/rector-src/');
        $this->symfonyStyle->note($noteMessage);
        return self::SUCCESS;
    }
    /**
     * @return CiDetector::CI_*|null
     */
    private function resolveCurrentCI() : ?string
    {
        if (\file_exists(\getcwd() . '/.github')) {
            return CiDetector::CI_GITHUB_ACTIONS;
        }
        if (\file_exists(\getcwd() . '/.gitlab-ci.yml')) {
            return CiDetector::CI_GITLAB;
        }
        return null;
    }
    private function addGithubActionsWorkflow(string $currentRepository, string $targetWorkflowFilePath) : void
    {
        $workflowTemplate = FileSystem::read(__DIR__ . '/../../../templates/rector-github-action-check.yaml');
        $workflowContents = \strtr($workflowTemplate, ['__CURRENT_REPOSITORY__' => $currentRepository]);
        FileSystem::write($targetWorkflowFilePath, $workflowContents, null);
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->success('The ".github/workflows/rector.yaml" file was added');
        $this->symfonyStyle->writeln('<comment>2 more steps to run Rector in CI:</comment>');
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('1) Generate token with "repo" scope:' . \PHP_EOL . 'https://github.com/settings/tokens/new');
        $this->symfonyStyle->newLine();
        $repositoryNewSecretsLink = sprintf('https://github.com/%s/settings/secrets/actions/new', $currentRepository);
        $this->symfonyStyle->writeln('2) Add the token to Action secrets as "ACCESS_TOKEN":' . \PHP_EOL . $repositoryNewSecretsLink);
    }
    private function addGitlabFile(string $targetGitlabFilePath) : void
    {
        $gitlabTemplate = FileSystem::read(__DIR__ . '/../../../templates/rector-gitlab-check.yaml');
        FileSystem::write($targetGitlabFilePath, $gitlabTemplate, null);
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->success('The "gitlab/rector.yaml" file was added');
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->writeln('1) Register it in your ".gitlab-ci.yml" file:' . \PHP_EOL . 'include:' . \PHP_EOL . '    - local: gitlab/rector.yaml');
    }
    /**
     * @return self::SUCCESS
     */
    private function handleGitlabCi() : int
    {
        // add snippet in the end of file or include it?
        $ciRectorFilePath = \getcwd() . '/gitlab/rector.yaml';
        if (\file_exists($ciRectorFilePath)) {
            $response = $this->symfonyStyle->ask('The "gitlab/rector.yaml" workflow already exists. Overwrite it?', 'Yes');
            if (!\in_array($response, ['y', 'yes', 'Yes'], \true)) {
                $this->symfonyStyle->note('Nothing changed');
                return self::SUCCESS;
            }
        }
        $this->addGitlabFile($ciRectorFilePath);
        return self::SUCCESS;
    }
    /**
     * @return self::SUCCESS|self::FAILURE
     */
    private function handleGithubActions() : int
    {
        $rectorWorkflowFilePath = \getcwd() . '/.github/workflows/rector.yaml';
        if (\file_exists($rectorWorkflowFilePath)) {
            $response = $this->symfonyStyle->ask('The "rector.yaml" workflow already exists. Overwrite it?', 'Yes');
            if (!\in_array($response, ['y', 'yes', 'Yes'], \true)) {
                $this->symfonyStyle->note('Nothing changed');
                return self::SUCCESS;
            }
        }
        $currentRepository = RepositoryHelper::resolveGithubRepositoryName(\getcwd());
        if ($currentRepository === null) {
            $this->symfonyStyle->error('Current repository name could not be resolved');
            return self::FAILURE;
        }
        $this->addGithubActionsWorkflow($currentRepository, $rectorWorkflowFilePath);
        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

use Httpful\Request;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Inspired from @see https://github.com/phpstan/phpstan-src/blob/master/bin/generate-changelog.php
 *
 * Usage:
 * GITHUB_TOKEN=<github_token> php bin/generate-changelog.php <from-commit> <to-commit> >> <file_to_dump.md>
 * GITHUB_TOKEN=ghp_... php bin/generate-changelog.php 07736c1 cb74bb6 >> CHANGELOG_dumped.md
 */
final class GenerateChangelogCommand extends Symfony\Component\Console\Command\Command
{
    /**
     * @var string
     */
    private const DEPLOY_REPOSITORY_NAME = 'rectorphp/rector';

    /**
     * @var string
     */
    private const DEVELOPMENT_REPOSITORY_NAME = 'rectorphp/rector-src';

    /**
     * @var string
     */
    private const OPTION_FROM_COMMIT = 'from-commit';

    /**
     * @var string
     */
    private const OPTION_TO_COMMIT = 'to-commit';

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->addArgument(self::OPTION_FROM_COMMIT, InputArgument::REQUIRED);
        $this->addArgument(self::OPTION_TO_COMMIT, InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commitHashRange = sprintf(
            '%s..%s',
            $input->getArgument(self::OPTION_FROM_COMMIT),
            $input->getArgument(self::OPTION_TO_COMMIT)
        );

        $commitLines = $this->exec(['git', 'log', $commitHashRange, '--reverse', '--pretty=%H %s']);

        $commits = array_map(function (string $line): array {
            [$hash, $message] = explode(' ', $line, 2);
            return [
                'hash' => $hash,
                'message' => $message,
            ];
        }, explode("\n", $commitLines));

        $i = 0;

        foreach ($commits as $commit) {
            $searchPullRequestsUri = sprintf(
                'https://api.github.com/search/issues?q=repo:' . self::DEVELOPMENT_REPOSITORY_NAME . '+%s',
                $commit['hash']
            );

            $searchPullRequestsResponse = Request::get($searchPullRequestsUri)
                ->sendsAndExpectsType('application/json')
                ->basicAuth('tomasvotruba', getenv('GITHUB_TOKEN'))
                ->send();

            if ($searchPullRequestsResponse->code !== 200) {
                $output->writeln(var_export($searchPullRequestsResponse->body, true));
                throw new InvalidArgumentException((string) $searchPullRequestsResponse->code);
            }

            $searchPullRequestsResponse = $searchPullRequestsResponse->body;

            $searchIssuesUri = sprintf(
                'https://api.github.com/search/issues?q=repo:' . self::DEPLOY_REPOSITORY_NAME . '+%s',
                $commit['hash']
            );

            $searchIssuesResponse = Request::get($searchIssuesUri)
                ->sendsAndExpectsType('application/json')
                ->basicAuth('tomasvotruba', getenv('GITHUB_TOKEN'))
                ->send();

            if ($searchIssuesResponse->code !== 200) {
                $output->writeln(var_export($searchIssuesResponse->body, true));
                throw new InvalidArgumentException((string) $searchIssuesResponse->code);
            }

            $searchIssuesResponse = $searchIssuesResponse->body;
            $items = array_merge($searchPullRequestsResponse->items, $searchIssuesResponse->items);
            $parenthesis = 'https://github.com/' . self::DEVELOPMENT_REPOSITORY_NAME . '/commit/' . $commit['hash'];
            $thanks = null;
            $issuesToReference = [];

            foreach ($items as $responseItem) {
                if (isset($responseItem->pull_request)) {
                    $parenthesis = sprintf(
                        '[#%d](%s)',
                        $responseItem->number,
                        'https://github.com/' . self::DEVELOPMENT_REPOSITORY_NAME . '/pull/' . $responseItem->number
                    );
                    $thanks = $responseItem->user->login;
                } else {
                    $issuesToReference[] = sprintf('#%d', $responseItem->number);
                }
            }

            $output->writeln(
                sprintf('* %s (%s)%s%s', $commit['message'], $parenthesis, count(
                    $issuesToReference
                ) > 0 ? ', ' . implode(
                    ', ',
                    $issuesToReference
                ) : '', $thanks !== null ? sprintf(', Thanks @%s!', $thanks) : '')
            );
            if ($i > 0 && $i % 8 === 0) {
                sleep(60);
            }

            ++$i;
        }

        return self::SUCCESS;
    }

    /**
     * @param string[] $commandParts
     */
    private function exec(array $commandParts): string
    {
        $process = new Symfony\Component\Process\Process($commandParts);
        $process->run();

        return $process->getOutput();
    }
}

$generateChangelogCommand = new GenerateChangelogCommand();

$application = new Application();
$application->add($generateChangelogCommand);
$application->setDefaultCommand(CommandNaming::classToName($generateChangelogCommand::class), true);
$application->run();

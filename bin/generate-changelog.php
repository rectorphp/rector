<?php

declare(strict_types=1);

use Httpful\Request;
use Nette\Utils\Strings;
use Symfony\Component\Console\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Inspired from @see https://github.com/phpstan/phpstan-src/blob/master/bin/generate-changelog.php
 *
 * Usage:
 * GITHUB_TOKEN=<github_token> php bin/generate-changelog.php <from-commit> <to-commit> >> <file_to_dump.md>
 * GITHUB_TOKEN=ghp_... php bin/generate-changelog.php 07736c1 cb74bb6 >> CHANGELOG_dumped.md
 */
final class GenerateChangelogCommand extends Command
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
     * @var string[]
     */
    private const EXCLUDED_THANKS_NAMES = ['TomasVotruba'];

    /**
     * @var string
     */
    private const OPTION_FROM_COMMIT = 'from-commit';

    /**
     * @var string
     */
    private const OPTION_TO_COMMIT = 'to-commit';

    /**
     * @var string
     */
    private const HASH = 'hash';

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->addArgument(self::OPTION_FROM_COMMIT, InputArgument::REQUIRED);
        $this->addArgument(self::OPTION_TO_COMMIT, InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fromCommit = (string) $input->getArgument(self::OPTION_FROM_COMMIT);
        $toCommit = (string) $input->getArgument(self::OPTION_TO_COMMIT);

        $commitLines = $this->resolveCommitLinesFromToHashes($fromCommit, $toCommit);

        $commits = array_map(function (string $line): array {
            [$hash, $message] = explode(' ', $line, 2);
            return [
                self::HASH => $hash,
                'message' => $message,
            ];
        }, $commitLines);

        $i = 0;

        foreach ($commits as $commit) {
            $searchPullRequestsUri = sprintf(
                'https://api.github.com/search/issues?q=repo:' . self::DEVELOPMENT_REPOSITORY_NAME . '+%s',
                $commit[self::HASH]
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
                $commit[self::HASH]
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
            $parenthesis = 'https://github.com/' . self::DEVELOPMENT_REPOSITORY_NAME . '/commit/' . $commit[self::HASH];
            $thanks = null;
            $issuesToReference = [];

            foreach ($items as $item) {
                if (property_exists($item, 'pull_request') && $item->pull_request !== null) {
                    $parenthesis = sprintf(
                        '[#%d](%s)',
                        $item->number,
                        'https://github.com/' . self::DEVELOPMENT_REPOSITORY_NAME . '/pull/' . $item->number
                    );
                    $thanks = $item->user->login;
                } else {
                    $issuesToReference[] = sprintf('#%d', $item->number);
                }
            }

            // clean commit from duplicating issue number
            $commitMatch = Strings::match($commit['message'], '#(.*?)( \(\#\d+\))?$#ms');

            $commit = $commitMatch[1] ?? $commit['message'];

            $changelogLine = sprintf(
                '* %s (%s)%s%s',
                $commit,
                $parenthesis,
                $issuesToReference !== [] ? ', ' . implode(', ', $issuesToReference) : '',
                $this->createThanks($thanks)
            );

            $output->writeln($changelogLine);

            // not to throttle the GitHub API
            if ($i > 0 && $i % 8 === 0) {
                sleep(60);
            }

            ++$i;
        }

        return self::SUCCESS;
    }

    protected function createThanks(string|null $thanks): string
    {
        if ($thanks === null) {
            return '';
        }

        if (in_array($thanks, self::EXCLUDED_THANKS_NAMES, true)) {
            return '';
        }

        return sprintf(', Thanks @%s!', $thanks);
    }

    /**
     * @param string[] $commandParts
     */
    private function exec(array $commandParts): string
    {
        $process = new Process($commandParts);
        $process->run();

        return $process->getOutput();
    }

    /**
     * @return string[]
     */
    private function resolveCommitLinesFromToHashes(string $fromCommit, string $toCommit): array
    {
        $commitHashRange = sprintf('%s..%s', $fromCommit, $toCommit);

        $output = $this->exec(['git', 'log', $commitHashRange, '--reverse', '--pretty=%H %s']);
        $commitLines = explode("\n", $output);

        // remove empty values
        return array_filter($commitLines);
    }
}

$generateChangelogCommand = new GenerateChangelogCommand();

$application = new Application();
$application->add($generateChangelogCommand);
$application->setDefaultCommand(CommandNaming::classToName($generateChangelogCommand::class), true);
$application->run();

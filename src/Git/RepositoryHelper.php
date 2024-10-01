<?php

declare (strict_types=1);
namespace Rector\Git;

use RectorPrefix202410\Nette\Utils\Strings;
use RectorPrefix202410\Symfony\Component\Process\Process;
final class RepositoryHelper
{
    /**
     * @var string
     * @see https://regex101.com/r/etcmog/2
     */
    private const GITHUB_REPOSITORY_REGEX = '#github\\.com[:\\/](?<repository_name>.*?)\\.git#';
    public static function resolveGithubRepositoryName(string $currentDirectory) : ?string
    {
        // resolve current repository name
        $process = new Process(['git', 'remote', 'get-url', 'origin'], $currentDirectory, null, null, null);
        $process->run();
        $output = $process->getOutput();
        $match = Strings::match($output, self::GITHUB_REPOSITORY_REGEX);
        return $match['repository_name'] ?? null;
    }
}

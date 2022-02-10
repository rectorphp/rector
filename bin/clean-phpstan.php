<?php

declare(strict_types=1);

use Nette\Utils\Strings;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

require __DIR__ . '/../vendor/autoload.php';

final class CleanPhpstanCommand extends Command
{
    /**
     * @var string
     */
    private const FILE = 'phpstan.neon';

    /**
     * @var string
     * @see https://regex101.com/r/TAAOpH/2
     */
    private const MULTI_SPACE_REGEX = '#\s{2,}|\\e\[30;43m |\\e\[39;49m\\n#';

    /**
     * @var string
     * @see https://regex101.com/r/crQyI3/3
     */
    private const ONELINE_IGNORED_PATTERN_REGEX = '#(?<=Ignored error pattern) (?<content>\#.*\#)(?= was not matched in reported errors\.)#';

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! file_exists(self::FILE)) {
            $message = sprintf('File %s does not exists', self::FILE);
            $output->writeln($message);

            return self::FAILURE;
        }

        $originalContent = (string) file_get_contents(self::FILE);
        $newContent = str_replace(
            'reportUnmatchedIgnoredErrors: false',
            'reportUnmatchedIgnoredErrors: true',
            $originalContent
        );

        file_put_contents(self::FILE, $newContent);

        $process = new Process(['composer', 'phpstan']);
        $process->run();

        $result = $process->getOutput();
        $isFailure = str_contains($result, 'Ignored error pattern');

        $output->writeln($result);

        if (! $isFailure) {
            file_put_contents(self::FILE, $originalContent);
            return self::SUCCESS;
        }

        $output->writeln('Removing ignored pattern ...');

        $result = Strings::replace($result, self::MULTI_SPACE_REGEX, ' ');
        $result = str_replace('   ', '', $result);

        $matchAll = Strings::matchAll($result, self::ONELINE_IGNORED_PATTERN_REGEX);

        foreach ($matchAll as $match) {
            $newContent = str_replace('        - \'' . $match['content'] . '\'', '', $newContent);
        }

        $newContent = str_replace(
            'reportUnmatchedIgnoredErrors: true',
            'reportUnmatchedIgnoredErrors: false',
            $newContent
        );
        file_put_contents(self::FILE, $newContent);

        $process2 = new Process(['composer', 'phpstan']);
        $process2->run();

        $result = $process2->getOutput();
        $isFailure = str_contains($result, 'Ignored error pattern');

        if ($isFailure) {
            $output->writeln('There are still ignored errors that need to be cleaned up manually');
            $output->writeln($result);

            return self::FAILURE;
        }

        $output->writeln('Ignored errors removed');

        return self::SUCCESS;
    }
}

$command = new CleanPhpstanCommand();

$application = new Application();
$application->add($command);
$application->setDefaultCommand(CommandNaming::classToName($command::class), true);
$application->run();

<?php declare(strict_types=1);

namespace Rector\CodingStyle;

use Rector\Exception\Configuration\InvalidConfigurationException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use function Safe\sprintf;

final class AfterRectorCodingStyle
{
    /**
     * @var string
     */
    private const ECS_BIN_PATH = 'vendor/bin/ecs';

    /**
     * @var string
     */
    private const ECS_AFTER_RECTOR_CONFIG = __DIR__ . '/../../ecs-after-rector.yml';

    /**
     * @param string[] $source
     */
    public function apply(array $source): void
    {
        $this->validate();

        $command = array_merge(
            [self::ECS_BIN_PATH, 'check', '--config', self::ECS_AFTER_RECTOR_CONFIG, '--fix'],
            $source
        );

        $process = new Process($command);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function validate(): void
    {
        if (file_exists(self::ECS_BIN_PATH)) {
            return;
        }

        throw new InvalidConfigurationException(sprintf(
            'To active "--with-style" you need EasyCodingStandard.%sRun "composer require symplify/easy-coding-standard --dev" to get it.%sYou can also remove "--with-style" and run PHP_CodeSniffer or PHP-CS-Fixer on changed code instead.',
            PHP_EOL,
            PHP_EOL
        ));
    }
}

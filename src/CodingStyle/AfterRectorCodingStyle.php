<?php declare(strict_types=1);

namespace Rector\CodingStyle;

use Rector\Exception\Configuration\InvalidConfigurationException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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

        $command = sprintf(
            '%s check %s --config %s --fix',
            self::ECS_BIN_PATH,
            implode(' ', $source),
            self::ECS_AFTER_RECTOR_CONFIG
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

        throw new InvalidConfigurationException(sprintf('ECS bin file not found in "%s"', self::ECS_BIN_PATH));
    }
}

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
    private const ECS_BIN_PATH = __DIR__ . '/../../ecs-after-rector.yml';

    /**
     * @param string[] $source
     */
    public function apply(array $source): void
    {
        $this->validate();

        $command = sprintf('vendor/bin/ecs check %s --config %s --fix', implode(' ', $source), self::ECS_BIN_PATH);

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

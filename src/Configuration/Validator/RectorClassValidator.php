<?php declare(strict_types=1);

namespace Rector\Configuration\Validator;

use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\Validator\InvalidRectorClassException;
use Rector\YamlParser\Contract\Rector\YamlRectorInterface;

final class RectorClassValidator
{
    /**
     * @param string[] $rectors
     */
    public function validate(array $rectors): void
    {
        foreach ($rectors as $rector) {
            $this->ensureClassExists($rector);
            $this->ensureIsRector($rector);
        }
    }

    private function ensureClassExists(string $rector): void
    {
        if (class_exists($rector)) {
            return;
        }

        throw new InvalidRectorClassException(sprintf(
            'Rector "%s" was not found. Make sure class exists and is autoloaded.',
            $rector
        ));
    }

    private function ensureIsRector(string $rector): void
    {
        if (is_a($rector, RectorInterface::class, true)) {
            return;
        }

        if (is_a($rector, YamlRectorInterface::class, true)) {
            return;
        }

        throw new InvalidRectorClassException(sprintf(
            'Rector "%s" is not supported. Use class that implements "%s" or "%s".',
            $rector,
            RectorInterface::class,
            YamlRectorInterface::class
        ));
    }
}

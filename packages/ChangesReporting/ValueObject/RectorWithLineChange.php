<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\ValueObject;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Parallel\Contract\SerializableInterface;

final class RectorWithLineChange implements SerializableInterface
{
    /**
     * @var string
     */
    private const KEY_RECTOR_CLASS = 'rector_class';

    /**
     * @var string
     */
    private const KEY_LINE = 'line';

    /**
     * @var class-string<RectorInterface>
     */
    private string $rectorClass;

    /**
     * @param class-string<RectorInterface>|RectorInterface $rectorClass
     */
    public function __construct(
        string|RectorInterface $rectorClass,
        private int $line
    ) {
        if ($rectorClass instanceof RectorInterface) {
            $rectorClass = $rectorClass::class;
        }

        $this->rectorClass = $rectorClass;
    }

    /**
     * @return class-string<RectorInterface>
     */
    public function getRectorClass(): string
    {
        return $this->rectorClass;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @param array<string, mixed> $json
     */
    public static function decode(array $json): SerializableInterface
    {
        return new self($json[self::KEY_RECTOR_CLASS], $json[self::KEY_LINE]);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            self::KEY_RECTOR_CLASS => $this->rectorClass,
            self::KEY_LINE => $this->line,
        ];
    }
}

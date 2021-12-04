<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Application;

use Rector\Core\Contract\Rector\RectorInterface;
use Symplify\EasyParallel\Contract\SerializableInterface;

final class RectorError implements SerializableInterface
{
    /**
     * @param class-string<RectorInterface>|null $rectorClass
     */
    public function __construct(
        private readonly string $message,
        private readonly string $relativeFilePath,
        private readonly ?int $line = null,
        private readonly ?string $rectorClass = null
    ) {
    }

    public function getRelativeFilePath(): string
    {
        return $this->relativeFilePath;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    /**
     * @return class-string<RectorInterface>|null
     */
    public function getRectorClass(): ?string
    {
        return $this->rectorClass;
    }

    /**
     * @param array<string, mixed> $json
     */
    public static function decode(array $json): SerializableInterface
    {
        return new self($json['message'], $json['relative_file_path'], $json['line'], $json['rector_class'],);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'message' => $this->message,
            'relative_file_path' => $this->relativeFilePath,
            'line' => $this->line,
            'rector_class' => $this->rectorClass,
        ];
    }
}

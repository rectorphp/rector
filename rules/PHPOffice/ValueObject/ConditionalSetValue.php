<?php

declare(strict_types=1);

namespace Rector\PHPOffice\ValueObject;

final class ConditionalSetValue
{
    /**
     * @var string
     */
    private $oldMethod;

    /**
     * @var string
     */
    private $newGetMethod;

    /**
     * @var int
     */
    private $argPosition;

    /**
     * @var string
     */
    private $newSetMethod;

    /**
     * @var bool
     */
    private $hasRow = false;

    public function __construct(
        string $oldMethod,
        string $newGetMethod,
        string $newSetMethod,
        int $argPosition,
        bool $hasRow
    ) {
        $this->oldMethod = $oldMethod;
        $this->newGetMethod = $newGetMethod;
        $this->argPosition = $argPosition;
        $this->newSetMethod = $newSetMethod;
        $this->hasRow = $hasRow;
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getArgPosition(): int
    {
        return $this->argPosition;
    }

    public function getNewGetMethod(): string
    {
        return $this->newGetMethod;
    }

    public function getNewSetMethod(): string
    {
        return $this->newSetMethod;
    }

    public function hasRow(): bool
    {
        return $this->hasRow;
    }
}

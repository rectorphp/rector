<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\ValueObject;

final class EventInfo
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $constant;

    /**
     * @var string
     */
    private $eventClass;

    /**
     * @var string[]
     */
    private $oldStringAliases = [];

    /**
     * @var string[]
     */
    private $oldClassConstAliases = [];

    /**
     * @param string[] $oldStringAliases
     * @param string[] $oldClassConstAliases
     */
    public function __construct(
        array $oldStringAliases,
        array $oldClassConstAliases,
        string $class,
        string $constant,
        string $eventClass
    ) {
        $this->oldStringAliases = $oldStringAliases;
        $this->oldClassConstAliases = $oldClassConstAliases;
        $this->class = $class;
        $this->constant = $constant;
        $this->eventClass = $eventClass;
    }

    /**
     * @return string[]
     */
    public function getOldStringAliases(): array
    {
        return $this->oldStringAliases;
    }

    /**
     * @return string[]
     */
    public function getOldClassConstAliases(): array
    {
        return $this->oldClassConstAliases;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getConstant(): string
    {
        return $this->constant;
    }

    public function getEventClass(): string
    {
        return $this->eventClass;
    }
}

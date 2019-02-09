<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Event;

final class EventInfo
{
    /**
     * @var string[]
     */
    private $oldStringAliases = [];

    /**
     * @var string[]
     */
    private $oldClassConstAlaises = [];

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
     * @param string[] $oldStringAliases
     * @param string[] $oldClassConstAlaises
     */
    public function __construct(
        array $oldStringAliases,
        array $oldClassConstAlaises,
        string $class,
        string $constant,
        string $eventClass
    ) {
        $this->oldStringAliases = $oldStringAliases;
        $this->oldClassConstAlaises = $oldClassConstAlaises;
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
    public function getOldClassConstAlaises(): array
    {
        return $this->oldClassConstAlaises;
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

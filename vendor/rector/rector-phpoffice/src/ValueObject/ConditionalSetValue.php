<?php

declare (strict_types=1);
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
     * @var string
     */
    private $newSetMethod;
    /**
     * @var int
     */
    private $argPosition;
    /**
     * @var bool
     */
    private $hasRow;
    public function __construct(string $oldMethod, string $newGetMethod, string $newSetMethod, int $argPosition, bool $hasRow)
    {
        $this->oldMethod = $oldMethod;
        $this->newGetMethod = $newGetMethod;
        $this->newSetMethod = $newSetMethod;
        $this->argPosition = $argPosition;
        $this->hasRow = $hasRow;
    }
    public function getOldMethod() : string
    {
        return $this->oldMethod;
    }
    public function getArgPosition() : int
    {
        return $this->argPosition;
    }
    public function getNewGetMethod() : string
    {
        return $this->newGetMethod;
    }
    public function getNewSetMethod() : string
    {
        return $this->newSetMethod;
    }
    public function hasRow() : bool
    {
        return $this->hasRow;
    }
}

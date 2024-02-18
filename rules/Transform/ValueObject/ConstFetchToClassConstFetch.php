<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use Rector\Validation\RectorAssert;
final class ConstFetchToClassConstFetch
{
    /**
     * @readonly
     * @var string
     */
    private $oldConstName;
    /**
     * @readonly
     * @var string
     */
    private $newClassName;
    /**
     * @readonly
     * @var string
     */
    private $newConstName;
    public function __construct(string $oldConstName, string $newClassName, string $newConstName)
    {
        $this->oldConstName = $oldConstName;
        $this->newClassName = $newClassName;
        $this->newConstName = $newConstName;
        RectorAssert::constantName($this->oldConstName);
        RectorAssert::className($this->newClassName);
        RectorAssert::constantName($this->newConstName);
    }
    public function getOldConstName() : string
    {
        return $this->oldConstName;
    }
    public function getNewClassName() : string
    {
        return $this->newClassName;
    }
    public function getNewConstName() : string
    {
        return $this->newConstName;
    }
}

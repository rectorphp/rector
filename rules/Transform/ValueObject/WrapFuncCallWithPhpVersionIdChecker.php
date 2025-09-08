<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

final class WrapFuncCallWithPhpVersionIdChecker
{
    /**
     * @readonly
     */
    private string $functionName;
    /**
     * @readonly
     */
    private int $phpVersionId;
    public function __construct(string $functionName, int $phpVersionId)
    {
        $this->functionName = $functionName;
        $this->phpVersionId = $phpVersionId;
    }
    public function getFunctionName(): string
    {
        return $this->functionName;
    }
    public function getPhpVersionId(): int
    {
        return $this->phpVersionId;
    }
}

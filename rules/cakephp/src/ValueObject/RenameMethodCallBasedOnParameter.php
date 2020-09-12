<?php

declare(strict_types=1);

namespace Rector\CakePHP\ValueObject;

final class RenameMethodCallBasedOnParameter
{
    /**
     * @var string
     */
    private $oldMethod;

    /**
     * @var string
     */
    private $parameterName;

    /**
     * @var string
     */
    private $newMethod;

    /**
     * @var string
     */
    private $oldClass;

    public function __construct(string $oldClass, string $oldMethod, string $parameterName, string $newMethod)
    {
        $this->oldMethod = $oldMethod;
        $this->parameterName = $parameterName;
        $this->newMethod = $newMethod;
        $this->oldClass = $oldClass;
    }

    public function getOldMethod(): string
    {
        return $this->oldMethod;
    }

    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    public function getNewMethod(): string
    {
        return $this->newMethod;
    }

    public function getOldClass(): string
    {
        return $this->oldClass;
    }
}

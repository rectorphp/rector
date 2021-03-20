<?php

declare(strict_types=1);

namespace Rector\Restoration\ValueObject;

final class InferParamFromClassMethodReturn
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $paramMethod;

    /**
     * @var string
     */
    private $returnMethod;

    public function __construct(string $class, string $paramMethod, string $returnMethod)
    {
        $this->class = $class;
        $this->paramMethod = $paramMethod;
        $this->returnMethod = $returnMethod;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getParamMethod(): string
    {
        return $this->paramMethod;
    }

    public function getReturnMethod(): string
    {
        return $this->returnMethod;
    }
}

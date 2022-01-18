<?php

declare (strict_types=1);
namespace Rector\CakePHP\ValueObject;

final class RemoveIntermediaryMethod
{
    /**
     * @readonly
     * @var string
     */
    private $firstMethod;
    /**
     * @readonly
     * @var string
     */
    private $secondMethod;
    /**
     * @readonly
     * @var string
     */
    private $finalMethod;
    public function __construct(string $firstMethod, string $secondMethod, string $finalMethod)
    {
        $this->firstMethod = $firstMethod;
        $this->secondMethod = $secondMethod;
        $this->finalMethod = $finalMethod;
    }
    public function getFirstMethod() : string
    {
        return $this->firstMethod;
    }
    public function getSecondMethod() : string
    {
        return $this->secondMethod;
    }
    public function getFinalMethod() : string
    {
        return $this->finalMethod;
    }
}

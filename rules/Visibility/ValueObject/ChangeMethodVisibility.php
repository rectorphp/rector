<?php

declare (strict_types=1);
namespace Rector\Visibility\ValueObject;

use Rector\Validation\RectorAssert;
final class ChangeMethodVisibility
{
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $method;
    /**
     * @readonly
     */
    private int $visibility;
    public function __construct(string $class, string $method, int $visibility)
    {
        $this->class = $class;
        $this->method = $method;
        $this->visibility = $visibility;
        RectorAssert::className($class);
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getVisibility() : int
    {
        return $this->visibility;
    }
}

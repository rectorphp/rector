<?php

declare (strict_types=1);
namespace Rector\Visibility\ValueObject;

final class ChangeMethodVisibility
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $method;
    /**
     * @var int
     */
    private $visibility;
    public function __construct(string $class, string $method, int $visibility)
    {
        $this->class = $class;
        $this->method = $method;
        $this->visibility = $visibility;
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

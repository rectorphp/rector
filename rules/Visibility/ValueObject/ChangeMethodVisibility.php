<?php

declare (strict_types=1);
namespace Rector\Visibility\ValueObject;

use Rector\Core\Validation\RectorAssert;
final class ChangeMethodVisibility
{
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $method;
    /**
     * @readonly
     * @var int
     */
    private $visibility;
    public function __construct(string $class, string $method, int $visibility)
    {
        $this->class = $class;
        $this->method = $method;
        $this->visibility = $visibility;
        \Rector\Core\Validation\RectorAssert::className($class);
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

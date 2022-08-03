<?php

declare (strict_types=1);
namespace Rector\Privatization\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class ReplaceStringWithClassConstant
{
    /**
     * @var class-string
     * @readonly
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
    private $argPosition;
    /**
     * @var class-string
     * @readonly
     */
    private $classWithConstants;
    /**
     * @readonly
     * @var bool
     */
    private $caseInsensitive = \false;
    /**
     * @param class-string $class
     * @param class-string $classWithConstants
     */
    public function __construct(string $class, string $method, int $argPosition, string $classWithConstants, bool $caseInsensitive = \false)
    {
        $this->class = $class;
        $this->method = $method;
        $this->argPosition = $argPosition;
        $this->classWithConstants = $classWithConstants;
        $this->caseInsensitive = $caseInsensitive;
        RectorAssert::className($class);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    /**
     * @return class-string
     */
    public function getClassWithConstants() : string
    {
        return $this->classWithConstants;
    }
    public function getArgPosition() : int
    {
        return $this->argPosition;
    }
    public function isCaseInsensitive() : bool
    {
        return $this->caseInsensitive;
    }
}

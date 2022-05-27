<?php

declare (strict_types=1);
namespace Rector\CakePHP\ValueObject;

use PHPStan\Type\ObjectType;
final class ModalToGetSet
{
    /**
     * @readonly
     * @var string
     */
    private $getMethod;
    /**
     * @readonly
     * @var string
     */
    private $setMethod;
    /**
     * @readonly
     * @var string
     */
    private $type;
    /**
     * @readonly
     * @var string
     */
    private $unprefixedMethod;
    /**
     * @readonly
     * @var int
     */
    private $minimalSetterArgumentCount = 1;
    /**
     * @readonly
     * @var string|null
     */
    private $firstArgumentType;
    public function __construct(string $type, string $unprefixedMethod, ?string $getMethod = null, ?string $setMethod = null, int $minimalSetterArgumentCount = 1, ?string $firstArgumentType = null)
    {
        $this->type = $type;
        $this->unprefixedMethod = $unprefixedMethod;
        $this->minimalSetterArgumentCount = $minimalSetterArgumentCount;
        $this->firstArgumentType = $firstArgumentType;
        $this->getMethod = $getMethod ?? 'get' . \ucfirst($unprefixedMethod);
        $this->setMethod = $setMethod ?? 'set' . \ucfirst($unprefixedMethod);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->type);
    }
    public function getUnprefixedMethod() : string
    {
        return $this->unprefixedMethod;
    }
    public function getGetMethod() : string
    {
        return $this->getMethod;
    }
    public function getSetMethod() : string
    {
        return $this->setMethod;
    }
    public function getMinimalSetterArgumentCount() : int
    {
        return $this->minimalSetterArgumentCount;
    }
    public function getFirstArgumentType() : ?string
    {
        return $this->firstArgumentType;
    }
}

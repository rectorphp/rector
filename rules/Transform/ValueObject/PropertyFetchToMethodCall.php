<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class PropertyFetchToMethodCall
{
    /**
     * @readonly
     * @var string
     */
    private $oldType;
    /**
     * @readonly
     * @var string
     */
    private $oldProperty;
    /**
     * @readonly
     * @var string
     */
    private $newGetMethod;
    /**
     * @readonly
     * @var string|null
     */
    private $newSetMethod;
    /**
     * @var mixed[]
     * @readonly
     */
    private $newGetArguments = [];
    /**
     * @param mixed[] $newGetArguments
     */
    public function __construct(string $oldType, string $oldProperty, string $newGetMethod, ?string $newSetMethod = null, array $newGetArguments = [])
    {
        $this->oldType = $oldType;
        $this->oldProperty = $oldProperty;
        $this->newGetMethod = $newGetMethod;
        $this->newSetMethod = $newSetMethod;
        $this->newGetArguments = $newGetArguments;
        \Rector\Core\Validation\RectorAssert::className($oldType);
    }
    public function getOldObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->oldType);
    }
    public function getOldProperty() : string
    {
        return $this->oldProperty;
    }
    public function getNewGetMethod() : string
    {
        return $this->newGetMethod;
    }
    public function getNewSetMethod() : ?string
    {
        return $this->newSetMethod;
    }
    /**
     * @return mixed[]
     */
    public function getNewGetArguments() : array
    {
        return $this->newGetArguments;
    }
}

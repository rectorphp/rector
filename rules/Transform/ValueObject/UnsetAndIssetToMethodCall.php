<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class UnsetAndIssetToMethodCall
{
    /**
     * @readonly
     * @var string
     */
    private $type;
    /**
     * @readonly
     * @var string
     */
    private $issetMethodCall;
    /**
     * @readonly
     * @var string
     */
    private $unsedMethodCall;
    public function __construct(string $type, string $issetMethodCall, string $unsedMethodCall)
    {
        $this->type = $type;
        $this->issetMethodCall = $issetMethodCall;
        $this->unsedMethodCall = $unsedMethodCall;
        RectorAssert::className($type);
        RectorAssert::methodName($issetMethodCall);
        RectorAssert::methodName($unsedMethodCall);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->type);
    }
    public function getIssetMethodCall() : string
    {
        return $this->issetMethodCall;
    }
    public function getUnsedMethodCall() : string
    {
        return $this->unsedMethodCall;
    }
}

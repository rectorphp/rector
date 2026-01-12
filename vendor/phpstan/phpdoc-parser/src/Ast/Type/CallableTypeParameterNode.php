<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function trim;
class CallableTypeParameterNode implements Node
{
    use NodeAttributes;
    public \PHPStan\PhpDocParser\Ast\Type\TypeNode $type;
    public bool $isReference;
    public bool $isVariadic;
    /** @var string (may be empty) */
    public string $parameterName;
    public bool $isOptional;
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\TypeNode $type, bool $isReference, bool $isVariadic, string $parameterName, bool $isOptional)
    {
        $this->type = $type;
        $this->isReference = $isReference;
        $this->isVariadic = $isVariadic;
        $this->parameterName = $parameterName;
        $this->isOptional = $isOptional;
    }
    public function __toString(): string
    {
        $type = "{$this->type} ";
        $isReference = $this->isReference ? '&' : '';
        $isVariadic = $this->isVariadic ? '...' : '';
        $isOptional = $this->isOptional ? '=' : '';
        return trim("{$type}{$isReference}{$isVariadic}{$this->parameterName}") . $isOptional;
    }
    /**
     * @param array<string, mixed> $properties
     */
    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['type'], $properties['isReference'], $properties['isVariadic'], $properties['parameterName'], $properties['isOptional']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }
        return $instance;
    }
}

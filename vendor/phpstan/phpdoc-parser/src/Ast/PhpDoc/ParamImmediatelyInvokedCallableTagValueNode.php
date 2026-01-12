<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function trim;
class ParamImmediatelyInvokedCallableTagValueNode implements \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    public string $parameterName;
    /** @var string (may be empty) */
    public string $description;
    public function __construct(string $parameterName, string $description)
    {
        $this->parameterName = $parameterName;
        $this->description = $description;
    }
    public function __toString(): string
    {
        return trim("{$this->parameterName} {$this->description}");
    }
    /**
     * @param array<string, mixed> $properties
     */
    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['parameterName'], $properties['description']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }
        return $instance;
    }
}

<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function trim;
class TypelessParamTagValueNode implements \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    public bool $isReference;
    public bool $isVariadic;
    public string $parameterName;
    /** @var string (may be empty) */
    public string $description;
    public function __construct(bool $isVariadic, string $parameterName, string $description, bool $isReference)
    {
        $this->isReference = $isReference;
        $this->isVariadic = $isVariadic;
        $this->parameterName = $parameterName;
        $this->description = $description;
    }
    public function __toString() : string
    {
        $reference = $this->isReference ? '&' : '';
        $variadic = $this->isVariadic ? '...' : '';
        return trim("{$reference}{$variadic}{$this->parameterName} {$this->description}");
    }
}

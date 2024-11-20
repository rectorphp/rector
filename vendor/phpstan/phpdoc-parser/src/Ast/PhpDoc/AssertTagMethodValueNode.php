<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class AssertTagMethodValueNode implements \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    public TypeNode $type;
    public string $parameter;
    public string $method;
    public bool $isNegated;
    public bool $isEquality;
    /** @var string (may be empty) */
    public string $description;
    public function __construct(TypeNode $type, string $parameter, string $method, bool $isNegated, string $description, bool $isEquality)
    {
        $this->type = $type;
        $this->parameter = $parameter;
        $this->method = $method;
        $this->isNegated = $isNegated;
        $this->isEquality = $isEquality;
        $this->description = $description;
    }
    public function __toString() : string
    {
        $isNegated = $this->isNegated ? '!' : '';
        $isEquality = $this->isEquality ? '=' : '';
        return trim("{$isNegated}{$isEquality}{$this->type} {$this->parameter}->{$this->method}() {$this->description}");
    }
}

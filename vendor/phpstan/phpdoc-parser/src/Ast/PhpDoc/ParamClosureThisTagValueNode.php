<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class ParamClosureThisTagValueNode implements \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    /** @var TypeNode */
    public $type;
    /** @var string */
    public $parameterName;
    /** @var string (may be empty) */
    public $description;
    public function __construct(TypeNode $type, string $parameterName, string $description)
    {
        $this->type = $type;
        $this->parameterName = $parameterName;
        $this->description = $description;
    }
    public function __toString() : string
    {
        return trim("{$this->type} {$this->parameterName} {$this->description}");
    }
}

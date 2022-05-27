<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class AssertTagValueNode implements \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    /** @var TypeNode */
    public $type;
    /** @var string */
    public $parameter;
    /** @var bool */
    public $isNegated;
    /** @var string (may be empty) */
    public $description;
    public function __construct(TypeNode $type, string $parameter, bool $isNegated, string $description)
    {
        $this->type = $type;
        $this->parameter = $parameter;
        $this->isNegated = $isNegated;
        $this->description = $description;
    }
    public function __toString() : string
    {
        $isNegated = $this->isNegated ? '!' : '';
        return trim("{$this->type} {$isNegated}{$this->parameter} {$this->description}");
    }
}

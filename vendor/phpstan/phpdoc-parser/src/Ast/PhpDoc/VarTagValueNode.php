<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\NodeAttributes;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class VarTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;
    /** @var TypeNode */
    public $type;
    /** @var string (may be empty) */
    public $variableName;
    /** @var string (may be empty) */
    public $description;
    public function __construct(TypeNode $type, string $variableName, string $description)
    {
        $this->type = $type;
        $this->variableName = $variableName;
        $this->description = $description;
    }
    public function __toString() : string
    {
        return trim("{$this->type} " . trim("{$this->variableName} {$this->description}"));
    }
}

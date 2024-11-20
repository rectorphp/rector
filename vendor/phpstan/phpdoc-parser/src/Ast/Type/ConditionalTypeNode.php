<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ConditionalTypeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    public \PHPStan\PhpDocParser\Ast\Type\TypeNode $subjectType;
    public \PHPStan\PhpDocParser\Ast\Type\TypeNode $targetType;
    public \PHPStan\PhpDocParser\Ast\Type\TypeNode $if;
    public \PHPStan\PhpDocParser\Ast\Type\TypeNode $else;
    public bool $negated;
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\TypeNode $subjectType, \PHPStan\PhpDocParser\Ast\Type\TypeNode $targetType, \PHPStan\PhpDocParser\Ast\Type\TypeNode $if, \PHPStan\PhpDocParser\Ast\Type\TypeNode $else, bool $negated)
    {
        $this->subjectType = $subjectType;
        $this->targetType = $targetType;
        $this->if = $if;
        $this->else = $else;
        $this->negated = $negated;
    }
    public function __toString() : string
    {
        return sprintf('(%s %s %s ? %s : %s)', $this->subjectType, $this->negated ? 'is not' : 'is', $this->targetType, $this->if, $this->else);
    }
}

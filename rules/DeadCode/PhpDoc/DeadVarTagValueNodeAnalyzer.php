<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
final class DeadVarTagValueNodeAnalyzer
{
    /**
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(\Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator)
    {
        $this->typeComparator = $typeComparator;
    }
    public function isDead(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode $varTagValueNode, \PhpParser\Node\Stmt\Property $property) : bool
    {
        if ($property->type === null) {
            return \false;
        }
        if (!$this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($property->type, $varTagValueNode->type, $property)) {
            return \false;
        }
        return $varTagValueNode->description === '';
    }
}

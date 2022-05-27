<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer;
use Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
final class DeadReturnTagValueNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer
     */
    private $genericTypeNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer
     */
    private $mixedArrayTypeNodeAnalyzer;
    public function __construct(\Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer $genericTypeNodeAnalyzer, \Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer $mixedArrayTypeNodeAnalyzer)
    {
        $this->typeComparator = $typeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->genericTypeNodeAnalyzer = $genericTypeNodeAnalyzer;
        $this->mixedArrayTypeNodeAnalyzer = $mixedArrayTypeNodeAnalyzer;
    }
    public function isDead(\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $returnTagValueNode, \PhpParser\Node\FunctionLike $functionLike) : bool
    {
        $returnType = $functionLike->getReturnType();
        if ($returnType === null) {
            return \false;
        }
        $classLike = $this->betterNodeFinder->findParentType($functionLike, \PhpParser\Node\Stmt\ClassLike::class);
        if ($classLike instanceof \PhpParser\Node\Stmt\Trait_ && $returnTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\ThisTypeNode) {
            return \false;
        }
        if (!$this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($returnType, $returnTagValueNode->type, $functionLike)) {
            return \false;
        }
        if (\in_array(\get_class($returnTagValueNode->type), \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger::ALLOWED_TYPES, \true)) {
            return \false;
        }
        if (!$returnTagValueNode->type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode) {
            return $returnTagValueNode->description === '';
        }
        if ($this->genericTypeNodeAnalyzer->hasGenericType($returnTagValueNode->type)) {
            return \false;
        }
        if ($this->mixedArrayTypeNodeAnalyzer->hasMixedArrayType($returnTagValueNode->type)) {
            return \false;
        }
        if ($this->hasTruePseudoType($returnTagValueNode->type)) {
            return \false;
        }
        return $returnTagValueNode->description === '';
    }
    private function hasTruePseudoType(\Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode) : bool
    {
        $unionTypes = $bracketsAwareUnionTypeNode->types;
        foreach ($unionTypes as $unionType) {
            if (!$unionType instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode) {
                continue;
            }
            $name = \strtolower((string) $unionType);
            if ($name === 'true') {
                return \true;
            }
        }
        return \false;
    }
}

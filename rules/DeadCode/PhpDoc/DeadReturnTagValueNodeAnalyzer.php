<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer;
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
    public function __construct(\Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer $genericTypeNodeAnalyzer)
    {
        $this->typeComparator = $typeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->genericTypeNodeAnalyzer = $genericTypeNodeAnalyzer;
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
        if (\in_array(\get_class($returnTagValueNode->type), [\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode::class, \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode::class], \true)) {
            return \false;
        }
        if (!$returnTagValueNode->type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode) {
            return $returnTagValueNode->description === '';
        }
        if (!$this->genericTypeNodeAnalyzer->hasGenericType($returnTagValueNode->type)) {
            return $returnTagValueNode->description === '';
        }
        return \false;
    }
}

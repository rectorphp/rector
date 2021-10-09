<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\NarrowUnionTypeDocRectorTest
 */
final class NarrowUnionTypeDocRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;
    public function __construct(\Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer $unionTypeAnalyzer)
    {
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes docblock by narrowing type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass {
    /**
     * @param object|DateTime $message
     */
    public function getMessage(object $message)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass {
    /**
     * @param DateTime $message
     */
    public function getMessage(object $message)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $params = $node->getParams();
        foreach ($params as $key => $param) {
            /** @var string $paramName */
            $paramName = $this->getName($param->var);
            $paramType = $phpDocInfo->getParamType($paramName);
            if (!$paramType instanceof \PHPStan\Type\UnionType) {
                continue;
            }
            if ($this->unionTypeAnalyzer->isScalar($paramType)) {
                $this->changeDocObjectScalar($key, $phpDocInfo);
                continue;
            }
            if ($this->unionTypeAnalyzer->hasObjectWithoutClassType($paramType)) {
                $this->changeDocObjectWithoutClassType($paramType, $key, $phpDocInfo);
            }
        }
        if ($phpDocInfo->hasChanged()) {
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED, \true);
            return $node;
        }
        return null;
    }
    private function changeDocObjectWithoutClassType(\PHPStan\Type\UnionType $unionType, int $key, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        if (!$this->unionTypeAnalyzer->hasObjectWithoutClassTypeWithOnlyFullyQualifiedObjectType($unionType)) {
            return;
        }
        $types = $unionType->getTypes();
        $resultType = '';
        foreach ($types as $type) {
            if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
                $resultType .= $type->getClassName() . '|';
            }
        }
        $resultType = \rtrim($resultType, '|');
        $paramTagValueNodes = $phpDocInfo->getParamTagValueNodes();
        if (isset($paramTagValueNodes[$key])) {
            $paramTagValueNodes[$key]->type = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($resultType);
        }
    }
    private function changeDocObjectScalar(int $key, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        $paramTagValueNodes = $phpDocInfo->getParamTagValueNodes();
        if (isset($paramTagValueNodes[$key])) {
            $paramTagValueNodes[$key]->type = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('scalar');
        }
    }
}

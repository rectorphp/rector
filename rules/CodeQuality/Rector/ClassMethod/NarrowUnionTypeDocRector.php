<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\NarrowUnionTypeDocRector\NarrowUnionTypeDocRectorTest
 */
final class NarrowUnionTypeDocRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer
     */
    private $unionTypeAnalyzer;
    public function __construct(UnionTypeAnalyzer $unionTypeAnalyzer)
    {
        $this->unionTypeAnalyzer = $unionTypeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes docblock by narrowing type', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $params = $node->getParams();
        $hasChanged = \false;
        foreach ($params as $key => $param) {
            /** @var string $paramName */
            $paramName = $this->getName($param->var);
            $paramType = $phpDocInfo->getParamType($paramName);
            if (!$paramType instanceof UnionType) {
                continue;
            }
            if ($this->unionTypeAnalyzer->isScalar($paramType)) {
                $this->changeDocObjectScalar($key, $phpDocInfo);
                $hasChanged = \true;
                continue;
            }
            if ($this->unionTypeAnalyzer->hasObjectWithoutClassType($paramType)) {
                $this->changeDocObjectWithoutClassType($paramType, $key, $phpDocInfo);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function changeDocObjectWithoutClassType(UnionType $unionType, int $key, PhpDocInfo $phpDocInfo) : void
    {
        if (!$this->unionTypeAnalyzer->hasObjectWithoutClassTypeWithOnlyFullyQualifiedObjectType($unionType)) {
            return;
        }
        $types = $unionType->getTypes();
        $resultType = '';
        foreach ($types as $type) {
            if ($type instanceof FullyQualifiedObjectType) {
                $resultType .= $type->getClassName() . '|';
            }
        }
        $resultType = \rtrim($resultType, '|');
        $paramTagValueNodes = $phpDocInfo->getParamTagValueNodes();
        if (isset($paramTagValueNodes[$key])) {
            $paramTagValueNodes[$key]->type = new IdentifierTypeNode($resultType);
        }
    }
    private function changeDocObjectScalar(int $key, PhpDocInfo $phpDocInfo) : void
    {
        $paramTagValueNodes = $phpDocInfo->getParamTagValueNodes();
        if (isset($paramTagValueNodes[$key])) {
            $paramTagValueNodes[$key]->type = new IdentifierTypeNode('scalar');
        }
    }
}

<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\NodeFinder\ArrayMapClosureExprFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockBasedOnArrayMapRector\AddParamArrayDocblockBasedOnArrayMapRectorTest
 */
final class AddParamArrayDocblockBasedOnArrayMapRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArrayMapClosureExprFinder $arrayMapClosureExprFinder;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(ArrayMapClosureExprFinder $arrayMapClosureExprFinder, StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->arrayMapClosureExprFinder = $arrayMapClosureExprFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param array docblock if array_map is used on the parameter', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(array $names): void
    {
        $names = array_map(fn(string $name) => trim($name), $names);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param string[] $names
     */
    public function run(array $names): void
    {
        $names = array_map(fn(string $name) => trim($name), $names);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getParams() === []) {
            return null;
        }
        $hasChanged = \false;
        $functionPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        foreach ($node->params as $param) {
            // handle only arrays
            if (!$this->isArrayParam($param)) {
                continue;
            }
            $paramName = $this->getName($param);
            $arrayMapClosures = $this->arrayMapClosureExprFinder->findByVariableName($node, $paramName);
            if ($arrayMapClosures === []) {
                continue;
            }
            foreach ($arrayMapClosures as $arrayMapClosure) {
                $params = $arrayMapClosure->getParams();
                if ($params === []) {
                    continue;
                }
                $firstParam = $params[0];
                $paramTypeNode = $firstParam->type;
                if ($paramTypeNode === null) {
                    continue;
                }
                if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($functionPhpDocInfo->getParamTagValueByName($paramName))) {
                    continue;
                }
                $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramTypeNode);
                $arrayParamType = new ArrayType(new MixedType(), $paramType);
                if ($this->nodeDocblockTypeDecorator->decorateGenericIterableParamType($arrayParamType, $functionPhpDocInfo, $node, $param, $paramName)) {
                    $hasChanged = \true;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function isArrayParam(Param $param): bool
    {
        if (!$param->type instanceof Identifier) {
            return \false;
        }
        return $this->isName($param->type, 'array');
    }
}

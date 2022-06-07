<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\ClassMethod\RenderMethodParamToTypeDeclarationRector\RenderMethodParamToTypeDeclarationRectorTest
 */
final class RenderMethodParamToTypeDeclarationRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer
     */
    private $paramTypeInferer;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover
     */
    private $paramTagRemover;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer
     */
    private $controllerRenderMethodAnalyzer;
    public function __construct(ParamTypeInferer $paramTypeInferer, ParamTagRemover $paramTagRemover, ControllerRenderMethodAnalyzer $controllerRenderMethodAnalyzer)
    {
        $this->paramTypeInferer = $paramTypeInferer;
        $this->paramTagRemover = $paramTagRemover;
        $this->controllerRenderMethodAnalyzer = $controllerRenderMethodAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move @param declarations on render() method in Nette components and presenter to strict type declarations', [new CodeSample(<<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

final class SomeControl extends Control
{
    /**
     * @param string $name
     */
    public function render($name)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

final class SomeControl extends Control
{
    public function render(string $name)
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
    public function refactor(Node $node)
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        if (!$this->controllerRenderMethodAnalyzer->isRenderMethod($node)) {
            return null;
        }
        // $node->getParams()
        foreach ($node->params as $param) {
            $this->refactorParam($param, $phpDocInfo, $node);
        }
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    private function refactorParam(Param $param, PhpDocInfo $phpDocInfo, ClassMethod $classMethod) : void
    {
        if ($param->type !== null) {
            return;
        }
        $inferedType = $this->paramTypeInferer->inferParam($param);
        $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType, TypeKind::PARAM);
        if (!$paramType instanceof Node) {
            return;
        }
        $param->type = $paramType;
        $this->hasChanged = \true;
        $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $classMethod);
    }
}

<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\RenderMethodParamToTypeDeclarationRector\RenderMethodParamToTypeDeclarationRectorTest
 */
final class RenderMethodParamToTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer
     */
    private $paramTypeInferer;
    /**
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover
     */
    private $paramTagRemover;
    /**
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer
     */
    private $controllerRenderMethodAnalyzer;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\ParamTypeInferer $paramTypeInferer, \Rector\DeadCode\PhpDoc\TagRemover\ParamTagRemover $paramTagRemover, \Rector\TypeDeclaration\NodeAnalyzer\ControllerRenderMethodAnalyzer $controllerRenderMethodAnalyzer)
    {
        $this->paramTypeInferer = $paramTypeInferer;
        $this->paramTagRemover = $paramTagRemover;
        $this->controllerRenderMethodAnalyzer = $controllerRenderMethodAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move @param docs on render() method in Symfony controller to strict type declaration', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends AbstractController
{
    /**
     * @Route()
     * @param string $name
     */
    public function render($name)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends AbstractController
{
    /**
     * @Route()
     */
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node)
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$this->controllerRenderMethodAnalyzer->isRenderMethod($node, $scope)) {
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
    private function refactorParam(\PhpParser\Node\Param $param, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        if ($param->type !== null) {
            return;
        }
        $inferedType = $this->paramTypeInferer->inferParam($param);
        $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($inferedType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM());
        if (!$paramType instanceof \PhpParser\Node) {
            return;
        }
        $param->type = $paramType;
        $this->hasChanged = \true;
        $this->paramTagRemover->removeParamTagsIfUseless($phpDocInfo, $classMethod);
    }
}

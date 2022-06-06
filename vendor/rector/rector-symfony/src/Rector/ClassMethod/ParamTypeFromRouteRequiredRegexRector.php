<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Symfony\NodeAnalyzer\RouteRequiredParamNameToTypesResolver;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\ParamTypeFromRouteRequiredRegexRector\ParamTypeFromRouteRequiredRegexRectorTest
 */
final class ParamTypeFromRouteRequiredRegexRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\TypeAnalyzer\ControllerAnalyzer
     */
    private $controllerAnalyzer;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\RouteRequiredParamNameToTypesResolver
     */
    private $routeRequiredParamNameToTypesResolver;
    public function __construct(\Rector\Symfony\TypeAnalyzer\ControllerAnalyzer $controllerAnalyzer, \Rector\Symfony\NodeAnalyzer\RouteRequiredParamNameToTypesResolver $routeRequiredParamNameToTypesResolver)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->routeRequiredParamNameToTypesResolver = $routeRequiredParamNameToTypesResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Complete strict param type declaration based on route annotation', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends Controller
{
    /**
     * @Route(
     *     requirements={"number"="\d+"},
     * )
     */
    public function detailAction($number)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends Controller
{
    /**
     * @Route(
     *     requirements={"number"="\d+"},
     * )
     */
    public function detailAction(int $number)
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
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        if (!$node->isPublic()) {
            return null;
        }
        $paramsToTypes = $this->routeRequiredParamNameToTypesResolver->resolve($node);
        if ($paramsToTypes === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($paramsToTypes as $paramName => $paramType) {
            $param = $this->findParamByName($node, $paramName);
            if (!$param instanceof \PhpParser\Node\Param) {
                continue;
            }
            if ($param->type !== null) {
                continue;
            }
            $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM);
            $param->type = $paramTypeNode;
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    private function findParamByName(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $paramName) : ?\PhpParser\Node\Param
    {
        foreach ($classMethod->getParams() as $param) {
            if (!$this->isName($param, $paramName)) {
                continue;
            }
            return $param;
        }
        return null;
    }
}

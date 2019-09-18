<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Symfony\ValueObject\SymfonyClass;
use Rector\ZendToSymfony\Collector\RouteCollector;
use Rector\ZendToSymfony\Detector\ZendDetector;
use Rector\ZendToSymfony\Resolver\ControllerMethodParamResolver;
use Rector\ZendToSymfony\ValueObject\RouteValueObject;

/**
 * @sponsor Thanks https://previo.cz/ for sponsoring this rule
 *
 * @see \Rector\ZendToSymfony\Tests\Rector\ClassMethod\GetParamToClassMethodParameterAndRouteRector\GetParamToClassMethodParameterAndRouteRectorTest
 */
final class GetParamToClassMethodParameterAndRouteRector extends AbstractRector
{
    /**
     * @var ControllerMethodParamResolver
     */
    private $controllerMethodParamResolver;

    /**
     * @var RouteCollector
     */
    private $routeCollector;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var ZendDetector
     */
    private $zendDetector;

    public function __construct(
        ZendDetector $zendDetector,
        ControllerMethodParamResolver $controllerMethodParamResolver,
        RouteCollector $routeCollector,
        DocBlockManipulator $docBlockManipulator
    ) {
        $this->controllerMethodParamResolver = $controllerMethodParamResolver;
        $this->routeCollector = $routeCollector;
        $this->docBlockManipulator = $docBlockManipulator;
        $this->zendDetector = $zendDetector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change $this->getParam() calls to action method arguments + Sdd symfony @Route',
            [new CodeSample(
                <<<'PHP'
public function someAction()
{
    $id = $this->getParam('id');
}
PHP
                ,
                <<<'PHP'
public function someAction($id)
{
}                
PHP
            )]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->zendDetector->isZendActionMethod($node)) {
            return null;
        }

        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        // every symfony action must return response
        $node->returnType = new FullyQualified(SymfonyClass::RESPONSE);

        // add params to arguments
        $paramNamesToParentNodes = $this->controllerMethodParamResolver->resolve($node);

        $addedParamNames = $this->addClassMethodParameters($node, $paramNamesToParentNodes);

        $methodName = $this->getName($node);
        $newMethodName = (string) Strings::before($methodName, 'Action');

        $routeValueObject = new RouteValueObject($className, $newMethodName, $addedParamNames);
        $this->routeCollector->addRouteValueObject($routeValueObject);

        $this->addRouteAnnotation($node, $routeValueObject);

        return $node;
    }

    private function removeGetParamAssignIfNotUseful(?Node $getParamParentNode): void
    {
        // e.g. $value = (int) $this->getParam('value');
        if ($getParamParentNode instanceof Cast) {
            $getParamParentNode = $getParamParentNode->getAttribute(AttributeKey::PARENT_NODE);
        }

        if (! $getParamParentNode instanceof Assign) {
            return;
        }

        if (! $getParamParentNode->var instanceof Variable) {
            return;
        }

        // matches: "$x = $this->getParam('x');"
        $this->removeNode($getParamParentNode);
    }

    private function addRouteAnnotation(ClassMethod $classMethod, RouteValueObject $routeValueObject): void
    {
        $this->docBlockManipulator->addTag($classMethod, $routeValueObject->getSymfonyRoutePhpDocTagNode());
    }

    /**
     * @param Param[] $paramNamesToParentNodes
     * @return string[]
     */
    private function addClassMethodParameters(ClassMethod $classMethod, array $paramNamesToParentNodes): array
    {
        $addedParamNames = [];
        foreach ($paramNamesToParentNodes as $paramName => $parentNode) {
            $this->removeGetParamAssignIfNotUseful($parentNode);

            if (in_array($paramName, $addedParamNames, true)) {
                continue;
            }

            $paramName = $this->correctParamNameBasedOnAssign($parentNode, $paramName);

            $classMethod->params[] = new Param(new Variable($paramName));
            $addedParamNames[] = $paramName;
        }
        return $addedParamNames;
    }

    private function correctParamNameBasedOnAssign(Node $parentNode, string $currentParamName): string
    {
        if (! $parentNode instanceof Assign) {
            return $currentParamName;
        }

        if (! $parentNode->var instanceof Variable) {
            return $currentParamName;
        }

        $assignVariableName = $this->getName($parentNode->var);
        // use the same variable name as assigned one, e.g.
        // $value = $this->getParam('some_value') → "value"
        if ($assignVariableName !== null) {
            return $assignVariableName;
        }

        return $currentParamName;
    }
}

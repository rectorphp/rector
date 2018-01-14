<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\HttpKernel;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Rector\Naming\PropertyNaming;
use Rector\Node\Attribute;
use Rector\Node\MethodCallNodeFactory;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeAnalyzer\Contrib\Symfony\ContainerCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

/**
 * Converts all:
 * - @template()
 * - public function indexAction() { }
 *
 * into:
 * - public function indexAction() {
 * -     $this->render('index.html.twig');
 * - }
 */
final class TemplateAnnotationRector extends AbstractRector
{
    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(DocBlockAnalyzer $docBlockAnalyzer, MethodCallNodeFactory $methodCallNodeFactory)
    {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        if (! $this->docBlockAnalyzer->hasAnnotation($node, 'template')) {
            return false;
        }

        return true;
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        // 1.remove annotation
        $this->docBlockAnalyzer->removeAnnotationFromNode($classMethodNode, 'template');

        // 2. derive template name
        $methodName = $classMethodNode->name->toString();
        $templateName = $this->resolveTemplateNameFromActionMethodName($methodName);

        // 3. add $this->render method call with template
        $thisRenderMethodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            'render',
            [new Node\Arg(new String_($templateName))]
        );

        // 4. to bottom of method - probably $methodCall->stmts[]
        $classMethodNode->stmts += $thisRenderMethodCall;

        return $classMethodNode;
    }

    private function resolveTemplateNameFromActionMethodName(string $methodName): string
    {
        if (Strings::startsWith($methodName, 'action')) {
            return substr($methodName, strlen('action')) . '.twig.html';
        }

        // @todo
    }
}

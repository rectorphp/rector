<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\HttpKernel;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use Rector\Node\MethodCallNodeFactory;
use Rector\Node\NodeFactory;
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

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NodeFinder
     */
    private $nodeFinder;

    public function __construct(
        DocBlockAnalyzer $docBlockAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory,
        NodeFactory $nodeFactory,
        NodeFinder $nodeFinder
    ) {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->nodeFactory = $nodeFactory;
        $this->nodeFinder = $nodeFinder;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        return $this->docBlockAnalyzer->hasAnnotation($node, 'template');
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

        $arguments = [$templateName];

        // has method return type?
        $secondArg = null;
        $returnNode = $this->nodeFinder->findFirstInstanceOf($classMethodNode->stmts, Return_::class);
        if ($returnNode instanceof Return_) {
            if ($returnNode->expr instanceof Array_ && count($returnNode->expr->items)) {
                $arguments[] = $returnNode->expr;
            }
        }

        // 3. add $this->render method call with template
        $thisRenderMethodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            'render',
            $this->nodeFactory->createArgs($arguments)
        );

        // 4. replace Return_ node value if exists
        if ($returnNode instanceof Return_) {
            $returnNode->expr = $thisRenderMethodCall;
        } else {
            // or add to the bottom of method
            $classMethodNode->stmts[] = new Return_($thisRenderMethodCall);
        }

        return $classMethodNode;
    }

    private function resolveTemplateNameFromActionMethodName(string $methodName): string
    {
        if (Strings::endsWith($methodName, 'Action')) {
            return substr($methodName, 0, -strlen('Action')) . '.html.twig';
        }

        // @todo - see @template docs for Symfony
    }
}

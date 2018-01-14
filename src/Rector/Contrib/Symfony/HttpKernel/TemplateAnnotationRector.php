<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\HttpKernel;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
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
 * - @Template()
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

        return $this->docBlockAnalyzer->hasAnnotation($node, 'Template');
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        /** @var Return_|null $returnNode */
        $returnNode = $this->nodeFinder->findFirstInstanceOf((array) $classMethodNode->stmts, Return_::class);

        // create "$this->render('template.file.twig.html', ['key' => 'value']);" method call
        $thisRenderMethodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            'render',
            $this->resolveRenderArguments($classMethodNode, $returnNode)
        );

        // replace Return_ node value if exists
        if ($returnNode) {
            $returnNode->expr = $thisRenderMethodCall;
        } else {
            // or add as last statement in the method
            $classMethodNode->stmts[] = new Return_($thisRenderMethodCall);
        }

        // remove annotation
        $this->docBlockAnalyzer->removeAnnotationFromNode($classMethodNode, 'Template');

        return $classMethodNode;
    }

    private function resolveTemplateNameFromActionMethodName(string $methodName): string
    {
        if (Strings::endsWith($methodName, 'Action')) {
            return substr($methodName, 0, -strlen('Action')) . '.html.twig';
        }

        // @todo - see @Template docs for Symfony
    }

    private function resolveTemplateName(ClassMethod $classMethodNode): string
    {
        $templateAnnotation = $this->docBlockAnalyzer->getTagsByName($classMethodNode, 'Template')[0];
        $content = $templateAnnotation->render();

        // @todo consider using sth similar to offical parsing
        $annotationContent = Strings::match($content, '#\("(?<filename>.*?)"\)#');
        if (isset($annotationContent['filename'])) {
            return $annotationContent['filename'];
        }

        return $this->resolveTemplateNameFromActionMethodName($classMethodNode->name->toString());
    }

    /**
     * @return Arg[]
     */
    private function resolveRenderArguments(ClassMethod $classMethodNode, ?Return_ $returnNode): array
    {
        $arguments = [$this->resolveTemplateName($classMethodNode)];

        if ($returnNode) {
            if ($returnNode->expr instanceof Array_ && count($returnNode->expr->items)) {
                $arguments[] = $returnNode->expr;
            }
        }

        return $this->nodeFactory->createArgs($arguments);
    }
}

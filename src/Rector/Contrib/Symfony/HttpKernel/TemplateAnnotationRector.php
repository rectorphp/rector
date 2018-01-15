<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\HttpKernel;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use Rector\Node\MethodCallNodeFactory;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\Contrib\Symfony\TemplateGuesser;
use Rector\NodeTraverserQueue\BetterNodeFinder;
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
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var TemplateGuesser
     */
    private $templateGuesser;

    public function __construct(
        DocBlockAnalyzer $docBlockAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory,
        NodeFactory $nodeFactory,
        BetterNodeFinder $betterNodeFinder,
        TemplateGuesser $templateGuesser
    ) {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->nodeFactory = $nodeFactory;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->templateGuesser = $templateGuesser;
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
        $returnNode = $this->betterNodeFinder->findLastInstanceOf((array) $classMethodNode->stmts, Return_::class);

        // create "$this->render('template.file.twig.html', ['key' => 'value']);" method call
        $renderArguments = $this->resolveRenderArguments($classMethodNode, $returnNode);
        $thisRenderMethodCall = $this->methodCallNodeFactory->createWithVariableNameMethodNameAndArguments(
            'this',
            'render',
            $renderArguments
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

    /**
     * @return Arg[]
     */
    private function resolveRenderArguments(ClassMethod $classMethodNode, ?Return_ $returnNode): array
    {
        $arguments = [$this->resolveTemplateName($classMethodNode)];
        if (! $returnNode) {
            return $this->nodeFactory->createArgs($arguments);

        }

        if ($returnNode->expr instanceof Array_ && count($returnNode->expr->items)) {
            $arguments[] = $returnNode->expr;
        }

        // already existing method call
        if ($returnNode->expr instanceof MethodCall) {
            foreach ($returnNode->expr->args as $arg) {
                if ($arg->value instanceof Array_) {
                    $arguments[] = $arg->value;
                }
            }
        }

        return $this->nodeFactory->createArgs($arguments);
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

        return $this->templateGuesser->resolveFromClassMethodNode($classMethodNode);
    }
}

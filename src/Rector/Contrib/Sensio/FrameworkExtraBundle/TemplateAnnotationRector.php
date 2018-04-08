<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Sensio\FrameworkExtraBundle;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Node\MethodCallNodeFactory;
use Rector\Node\NodeFactory;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\Rector\Contrib\Sensio\Helper\TemplateGuesser;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

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

    /**
     * @var int
     */
    private $version;

    public function __construct(
        int $version,
        DocBlockAnalyzer $docBlockAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory,
        NodeFactory $nodeFactory,
        BetterNodeFinder $betterNodeFinder,
        TemplateGuesser $templateGuesser
    ) {
        $this->version = $version;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
        $this->nodeFactory = $nodeFactory;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->templateGuesser = $templateGuesser;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns @Template annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony', [
            new CodeSample(
                '/** @Template() */ public function indexAction() { }', 'public function indexAction() {
 return $this->render("index.html.twig"); }'),
        ]);
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

        if (! $returnNode) {
            // or add as last statement in the method
            $classMethodNode->stmts[] = new Return_($thisRenderMethodCall);
        }

        // replace Return_ node value if exists and is not already in correct format
        if ($returnNode && ! $returnNode->expr instanceof MethodCall) {
            $returnNode->expr = $thisRenderMethodCall;
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

        $arguments = array_merge($arguments, $this->resolveArgumentsFromMethodCall($returnNode));

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

        return $this->templateGuesser->resolveFromClassMethodNode($classMethodNode, $this->version);
    }

    /**
     * Already existing method call
     * @return mixed[]
     */
    private function resolveArgumentsFromMethodCall(Return_ $returnNode): array
    {
        $arguments = [];
        if ($returnNode->expr instanceof MethodCall) {
            foreach ($returnNode->expr->args as $arg) {
                if ($arg->value instanceof Array_) {
                    $arguments[] = $arg->value;
                }
            }
        }

        return $arguments;
    }
}

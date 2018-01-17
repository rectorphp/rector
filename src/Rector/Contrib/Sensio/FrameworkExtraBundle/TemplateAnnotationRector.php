<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Sensio\FrameworkExtraBundle;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Exception\Rector\InvalidRectorConfigurationException;
use Rector\Node\MethodCallNodeFactory;
use Rector\Node\NodeFactory;
use Rector\NodeTraverserQueue\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\Rector\Contrib\Sensio\Helper\TemplateGuesser;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

/**
 * Converts all:
 * - @Template()
 * - public function indexAction() { }
 *
 * into:
 * - public function indexAction() {
 * -     return $this->render('index.html.twig');
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

    /**
     * @var int
     */
    private $version;

    /**
     * @param mixed[] $config
     */
    public function __construct(
        array $config,
        DocBlockAnalyzer $docBlockAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory,
        NodeFactory $nodeFactory,
        BetterNodeFinder $betterNodeFinder,
        TemplateGuesser $templateGuesser
    ) {
        $this->setConfig($config);
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

    /**
     * @param mixed[] $config
     */
    private function setConfig(array $config): void
    {
        $this->ensureConfigHasVersion($config);
        $this->version = $config['version'];
    }

    /**
     * @param mixed[] $config
     */
    private function ensureConfigHasVersion(array $config): void
    {
        if (isset($config['version'])) {
            return;
        }

        throw new InvalidRectorConfigurationException(sprintf(
            'Rector "%s" is missing "%s" configuration. Add it as "%s" to config.yml under its key"',
            self::class,
            'version',
            'version: <value>'
        ));
    }
}

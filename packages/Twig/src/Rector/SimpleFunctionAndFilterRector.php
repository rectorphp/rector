<?php declare(strict_types=1);

namespace Rector\Twig\Rector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Utils\NodeTraverser\CallableNodeTraverser;

/**
 * Covers https://twig.symfony.com/doc/1.x/deprecated.html#function
 */
final class SimpleFunctionAndFilterRector extends AbstractRector
{
    /**
     * @var string
     */
    private $twigExtensionClass;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var string
     */
    private $twigOldFunctionClass;

    /**
     * @var string
     */
    private $twigNewFunctionClass;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var string
     */
    private $twigOldFilterClass;

    /**
     * @var string
     */
    private $twigNewFilterClass;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        CallableNodeTraverser $callableNodeTraverser,
        string $twigExtensionClass = 'Twig_Extension',
        string $twigOldFunctionClass = 'Twig_Function_Method',
        string $twigNewFunctionClass = 'Twig_SimpleFunction',
        string $twigOldFilterClass = 'Twig_Filter_Method',
        string $twigNewFilterClass = 'Twig_SimpleFilter'
    ) {
        $this->twigExtensionClass = $twigExtensionClass;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->twigOldFunctionClass = $twigOldFunctionClass;
        $this->twigNewFunctionClass = $twigNewFunctionClass;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->twigOldFilterClass = $twigOldFilterClass;
        $this->twigNewFilterClass = $twigNewFilterClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes Twig_Function_Method to Twig_SimpleFunction calls in TwigExtension.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return [
            'is_mobile' => new Twig_Function_Method($this, 'isMobile'),
        ];
    }

    public function getFilters()
    {
        return [
            'is_mobile' => new Twig_Filter_Method($this, 'isMobile'),
        ];
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return [
             new Twig_SimpleFunction('is_mobile', [$this, 'isMobile']),
        ];
    }

    public function getFilteres()
    {
        return [
             new Twig_SimpleFilter('is_mobile', [$this, 'isMobile']),
        ];
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $returnNode
     */
    public function refactor(Node $returnNode): ?Node
    {
        $classNode = $returnNode->getAttribute(Attribute::CLASS_NODE);
        $classNodeTypes = $this->nodeTypeResolver->resolve($classNode);

        if (! in_array($this->twigExtensionClass, $classNodeTypes, true)) {
            return null;
        }

        $methodName = $returnNode->getAttribute(Attribute::METHOD_NAME);

        if (! in_array($methodName, ['getFunctions', 'getFilters'], true)) {
            return null;
        }

        $this->callableNodeTraverser->traverseNodesWithCallable([$returnNode->expr], function (Node $node) use (
            $methodName
        ) {
            if (! $node instanceof ArrayItem) {
                return null;
            }

            if (! $node->value instanceof New_) {
                return null;
            }

            $newNodeTypes = $this->nodeTypeResolver->resolve($node->value);

            if ($methodName === 'getFunctions') {
                return $this->processArrayItem(
                    $node,
                    $this->twigOldFunctionClass,
                    $this->twigNewFunctionClass,
                    $newNodeTypes
                );
            }

            if ($methodName === 'getFilters') {
                return $this->processArrayItem(
                    $node,
                    $this->twigOldFilterClass,
                    $this->twigNewFilterClass,
                    $newNodeTypes
                );
            }
        });

        return $returnNode;
    }

    /**
     * @param string[] $newNodeTypes
     */
    private function processArrayItem(ArrayItem $node, string $oldClass, string $newClass, array $newNodeTypes): ?Node
    {
        if (! in_array($oldClass, $newNodeTypes, true)) {
            return null;
        }

        if (! $node->key instanceof String_) {
            return null;
        }

        if (! $node->value instanceof New_) {
            return null;
        }

        // match!
        $filterName = $node->key->value;

        $node->key = null;

        $node->value->class = new FullyQualified($newClass);

        $oldArguments = $node->value->args;

        $arrayItems = [];
        foreach ($oldArguments as $oldArgument) {
            $arrayItems[] = new ArrayItem($oldArgument->value);
        }

        $node->value->args[0] = new Arg(new String_($filterName));
        $node->value->args[1] = new Arg(new Array_($arrayItems, [
            'kind' => Array_::KIND_SHORT,
        ]));

        return $node;
    }
}

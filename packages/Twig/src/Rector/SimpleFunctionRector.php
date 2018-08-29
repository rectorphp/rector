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
 * Covers https://twig.symfony.com/doc/1.x/deprecated.html#filters
 */
final class SimpleFunctionRector extends AbstractRector
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
    private $twigFunctionMethodClass;

    /**
     * @var string
     */
    private $twigSimpleFunctionClass;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        CallableNodeTraverser $callableNodeTraverser,
        string $twigExtensionClass = 'Twig_Extension',
        string $twigFunctionMethodClass = 'Twig_Filter_Method',
        string $twigSimpleFunctionClass = 'Twig_SimpleFunction'
    ) {
        $this->twigExtensionClass = $twigExtensionClass;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->twigFunctionMethodClass = $twigFunctionMethodClass;
        $this->twigSimpleFunctionClass = $twigSimpleFunctionClass;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes Twig_Filter_Method to Twig_SimpleFunction calls in TwigExtension.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeExtension extends Twig_Extension
{
    public function getFunctions()
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
        if ($methodName !== 'getFunctions') {
            return null;
        }

        $this->callableNodeTraverser->traverseNodesWithCallable([$returnNode->expr], function (Node $node) {
            if (! $node instanceof ArrayItem) {
                return null;
            }

            if (! $node->key instanceof String_) {
                return null;
            }

            if (! $node->value instanceof New_) {
                return null;
            }

            $newNodeTypes = $this->nodeTypeResolver->resolve($node->value);
            if (! in_array($this->twigFunctionMethodClass, $newNodeTypes, true)) {
                return null;
            }

            // match!
            $filterName = $node->key->value;

            $node->key = null;

            $node->value->class = new FullyQualified($this->twigSimpleFunctionClass);

            $oldArguments = $node->value->args;

            $arrayItems = [];
            foreach ($oldArguments as $oldArgument) {
                $arrayItems[] = new ArrayItem($oldArgument->value);
            }

            $node->value->args[0] = new Arg(new String_($filterName));
            $node->value->args[1] = new Arg(new Array_($arrayItems, ['kind' => Array_::KIND_SHORT]));
        });

        return $returnNode;
    }
}

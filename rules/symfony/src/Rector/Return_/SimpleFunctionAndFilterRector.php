<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Covers https://twig.symfony.com/doc/1.x/deprecated.html#function
 *
 * @see \Rector\Symfony\Tests\Rector\Return_\SimpleFunctionAndFilterRector\SimpleFunctionAndFilterRectorTest
 */
final class SimpleFunctionAndFilterRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_CLASSES = [
        'Twig_Function_Method' => 'Twig_SimpleFunction',
        'Twig_Filter_Method' => 'Twig_SimpleFilter',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes Twig_Function_Method to Twig_SimpleFunction calls in Twig_Extension.',
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

    public function getFilters()
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
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->expr === null) {
            return null;
        }

        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        if (! $this->isObjectType($classLike, 'Twig_Extension')) {
            return null;
        }

        $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);
        if (! in_array($methodName, ['getFunctions', 'getFilters'], true)) {
            return null;
        }

        $this->traverseNodesWithCallable($node->expr, function (Node $node): ?Node {
            if (! $node instanceof ArrayItem) {
                return null;
            }

            if (! $node->value instanceof New_) {
                return null;
            }

            return $this->processArrayItem($node, $this->getObjectType($node->value));
        });

        return $node;
    }

    private function processArrayItem(ArrayItem $arrayItem, Type $newNodeType): ArrayItem
    {
        foreach (self::OLD_TO_NEW_CLASSES as $oldClass => $newClass) {
            $oldClassObjectType = new ObjectType($oldClass);
            if (! $oldClassObjectType->equals($newNodeType)) {
                continue;
            }

            if (! $arrayItem->key instanceof String_) {
                continue;
            }

            if (! $arrayItem->value instanceof New_) {
                continue;
            }

            // match!
            $filterName = $this->valueResolver->getValue($arrayItem->key);

            $arrayItem->key = null;
            $arrayItem->value->class = new FullyQualified($newClass);

            $oldArguments = $arrayItem->value->args;

            $this->createNewArrayItem($arrayItem, $oldArguments, $filterName);

            return $arrayItem;
        }

        return $arrayItem;
    }

    /**
     * @param Arg[] $oldArguments
     */
    private function createNewArrayItem(ArrayItem $arrayItem, array $oldArguments, string $filterName): ArrayItem
    {
        /** @var New_ $new */
        $new = $arrayItem->value;

        if ($oldArguments[0]->value instanceof Array_) {
            // already array, just shift it
            $new->args = array_merge([new Arg(new String_($filterName))], $oldArguments);

            return $arrayItem;
        }

        // not array yet, wrap to one
        $arrayItems = [];
        foreach ($oldArguments as $oldArgument) {
            $arrayItems[] = new ArrayItem($oldArgument->value);
        }

        $new->args[0] = new Arg(new String_($filterName));
        $new->args[1] = new Arg(new Array_($arrayItems));

        return $arrayItem;
    }
}

<?php

declare (strict_types=1);
namespace Rector\Symfony\Twig134\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Covers https://twig.symfony.com/doc/1.x/deprecated.html#function
 *
 * @see \Rector\Symfony\Tests\Twig134\Rector\Return_\SimpleFunctionAndFilterRector\SimpleFunctionAndFilterRectorTest
 */
final class SimpleFunctionAndFilterRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var array<string, class-string>>
     */
    private const OLD_TO_NEW_CLASSES = ['Twig_Function_Method' => 'Twig_SimpleFunction', 'Twig_Filter_Method' => 'Twig_SimpleFilter'];
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes Twig_Function_Method to Twig_SimpleFunction calls in Twig_Extension.', [new CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        if ($this->shouldSkip($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Return_ || !$stmt->expr instanceof Expr) {
                continue;
            }
            $this->traverseNodesWithCallable($stmt->expr, function (Node $node) use(&$hasChanged) : ?Node {
                if (!$node instanceof ArrayItem) {
                    return null;
                }
                if (!$node->value instanceof New_) {
                    return null;
                }
                $newObjectType = $this->nodeTypeResolver->getType($node->value);
                $this->processArrayItem($node, $newObjectType, $hasChanged);
                return $node;
            });
            break;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkip(ClassMethod $classMethod) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        if (!$classReflection->isSubclassOf('Twig_Extension')) {
            return \true;
        }
        return !$this->nodeNameResolver->isNames($classMethod, ['getFunctions', 'getFilters']);
    }
    private function processArrayItem(ArrayItem $arrayItem, Type $newNodeType, bool &$hasChanged) : void
    {
        foreach (self::OLD_TO_NEW_CLASSES as $oldClass => $newClass) {
            $oldClassObjectType = new ObjectType($oldClass);
            if (!$oldClassObjectType->equals($newNodeType)) {
                continue;
            }
            if (!$arrayItem->key instanceof String_) {
                continue;
            }
            if (!$arrayItem->value instanceof New_) {
                continue;
            }
            // match!
            $filterName = $this->valueResolver->getValue($arrayItem->key);
            $arrayItem->key = null;
            $arrayItem->value->class = new FullyQualified($newClass);
            $oldArguments = $arrayItem->value->getArgs();
            $this->decorateArrayItem($arrayItem, $oldArguments, $filterName);
            $hasChanged = \true;
            break;
        }
    }
    /**
     * @param Arg[] $oldArguments
     */
    private function decorateArrayItem(ArrayItem $arrayItem, array $oldArguments, string $filterName) : void
    {
        /** @var New_ $new */
        $new = $arrayItem->value;
        if ($oldArguments[0]->value instanceof Array_) {
            // already array, just shift it
            $new->args = \array_merge([new Arg(new String_($filterName))], $oldArguments);
            return;
        }
        // not array yet, wrap to one
        $arrayItems = [];
        foreach ($oldArguments as $oldArgument) {
            $arrayItems[] = new ArrayItem($oldArgument->value);
        }
        $new->args[0] = new Arg(new String_($filterName));
        $new->args[1] = new Arg(new Array_($arrayItems));
    }
}

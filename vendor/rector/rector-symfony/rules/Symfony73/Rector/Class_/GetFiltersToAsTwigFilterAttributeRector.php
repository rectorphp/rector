<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\TwigClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/blog/new-in-symfony-7-3-twig-extension-attributes
 *
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\GetFiltersToAsTwigFilterAttributeRector\GetFiltersToAsTwigFilterAttributeRectorTest
 */
final class GetFiltersToAsTwigFilterAttributeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes getFilters() in TwigExtension to #[TwigFilter] marker attribute above function', [new CodeSample(<<<'CODE_SAMPLE'
use Twig\Extension\AbstractExtension;

class SomeClass extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('filter_name', [$this, 'localMethod']),
        ];
    }

    public function localMethod($value)
    {
        return $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SomeClass extends AbstractExtension
{
    #[TwigFilter('filter_name')]
    public function localMethod($value)
    {
        return $value;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if (!$this->reflectionProvider->hasClass(TwigClass::TWIG_EXTENSION)) {
            return null;
        }
        if ($node->isAbstract() || $node->isAnonymous()) {
            return null;
        }
        $twigExtensionObjectType = new ObjectType(TwigClass::TWIG_EXTENSION);
        if (!$this->isObjectType($node, $twigExtensionObjectType)) {
            return null;
        }
        $getFilterMethod = $node->getMethod('getFilters');
        if (!$getFilterMethod instanceof ClassMethod) {
            return null;
        }
        $hasChanged = \false;
        foreach ((array) $getFilterMethod->stmts as $stmt) {
            // handle return array simple case
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Array_) {
                continue;
            }
            $returnArray = $stmt->expr;
            foreach ($returnArray->items as $key => $arrayItem) {
                if (!$arrayItem->value instanceof New_) {
                    continue;
                }
                $new = $arrayItem->value;
                if (!$this->isObjectType($new->class, new ObjectType(TwigClass::TWIG_FILTER))) {
                    continue;
                }
                if (\count($new->getArgs()) !== 2) {
                    continue;
                }
                $secondArg = $new->getArgs()[1];
                if ($secondArg->value instanceof MethodCall && $secondArg->value->isFirstClassCallable() && $this->isObjectType($secondArg->value->var, $twigExtensionObjectType)) {
                    if ($this->processSetAttribute($secondArg, $node, $returnArray, $key)) {
                        $hasChanged = \true;
                    }
                } elseif ($secondArg->value instanceof Array_ && \count($secondArg->value->items) === 2) {
                    if ($this->processSetAttribute($secondArg, $node, $returnArray, $key)) {
                        $hasChanged = \true;
                    }
                }
            }
            if ($hasChanged) {
                $this->removeGetFilterMethodIfEmpty($returnArray, $node);
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function processSetAttribute(Arg $secondArg, Class_ $class, Array_ $returnArray, int $key) : bool
    {
        $localMethodName = $this->matchLocalMethodName($secondArg->value);
        if (!\is_string($localMethodName)) {
            return \false;
        }
        $localMethod = $class->getMethod($localMethodName);
        if (!$localMethod instanceof ClassMethod) {
            return \false;
        }
        $localMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(TwigClass::AS_TWIG_FILTER_ATTRIBUTE))]);
        // remove old new fuction instance
        unset($returnArray->items[$key]);
        return \true;
    }
    /**
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Expr\MethodCall $callable
     */
    private function matchLocalMethodName($callable) : ?string
    {
        if ($callable instanceof Array_) {
            $firstItem = $callable->items[0];
            if (!$firstItem->value instanceof Variable) {
                return null;
            }
            if (!$this->isName($firstItem->value, 'this')) {
                return null;
            }
            $secondItem = $callable->items[1];
            if (!$secondItem->value instanceof String_) {
                return null;
            }
            return $secondItem->value->value;
        }
        return $this->getName($callable->name);
    }
    private function removeGetFilterMethodIfEmpty(Array_ $getFilterReturnArray, Class_ $class) : void
    {
        if (\count($getFilterReturnArray->items) !== 0) {
            return;
        }
        // remove "getFilters()" method
        foreach ($class->stmts as $key => $classStmt) {
            if (!$classStmt instanceof ClassMethod) {
                continue;
            }
            if (!$this->isName($classStmt, 'getFilters')) {
                continue;
            }
            unset($class->stmts[$key]);
        }
    }
}

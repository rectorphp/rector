<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\WrapReturn;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202409\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\ClassMethod\WrapReturnRector\WrapReturnRectorTest
 */
final class WrapReturnRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var WrapReturn[]
     */
    private $typeMethodWraps = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Wrap return value of specific method', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function getItem()
    {
        return 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getItem()
    {
        return [1];
    }
}
CODE_SAMPLE
, [new WrapReturn('SomeClass', 'getItem', \true)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($this->typeMethodWraps as $typeMethodWrap) {
            if (!$this->isObjectType($node, $typeMethodWrap->getObjectType())) {
                continue;
            }
            foreach ($node->getMethods() as $classMethod) {
                if (!$this->isName($classMethod, $typeMethodWrap->getMethod())) {
                    continue;
                }
                if ($node->stmts === null) {
                    continue;
                }
                $this->wrap($classMethod, $typeMethodWrap->isArrayWrap());
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, WrapReturn::class);
        $this->typeMethodWraps = $configuration;
    }
    private function wrap(ClassMethod $classMethod, bool $isArrayWrap) : ?ClassMethod
    {
        if (!\is_iterable($classMethod->stmts)) {
            return null;
        }
        foreach ($classMethod->stmts as $key => $stmt) {
            if ($stmt instanceof Return_ && $stmt->expr instanceof Expr) {
                if ($isArrayWrap && !$stmt->expr instanceof Array_) {
                    $stmt->expr = new Array_([new ArrayItem($stmt->expr)]);
                }
                $classMethod->stmts[$key] = $stmt;
            }
        }
        return $classMethod;
    }
}

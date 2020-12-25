<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Generic\ValueObject\WrapReturn;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector\WrapReturnRectorTest
 */
final class WrapReturnRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPE_METHOD_WRAPS = 'type_method_wraps';

    /**
     * @var WrapReturn[]
     */
    private $typeMethodWraps = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Wrap return value of specific method', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getItem()
    {
        return 1;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getItem()
    {
        return [1];
    }
}
CODE_SAMPLE
                ,
                [
                    self::TYPE_METHOD_WRAPS => [new WrapReturn('SomeClass', 'getItem', true)],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->typeMethodWraps as $typeMethodWrap) {
            if (! $this->isObjectType($node, $typeMethodWrap->getType())) {
                continue;
            }

            if (! $this->isName($node, $typeMethodWrap->getMethod())) {
                continue;
            }

            if (! $node->stmts) {
                continue;
            }

            return $this->wrap($node, $typeMethodWrap->isArrayWrap());
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $typeMethodWraps = $configuration[self::TYPE_METHOD_WRAPS] ?? [];
        Assert::allIsInstanceOf($typeMethodWraps, WrapReturn::class);
        $this->typeMethodWraps = $typeMethodWraps;
    }

    private function wrap(ClassMethod $classMethod, bool $isArrayWrap): ?ClassMethod
    {
        if (! is_iterable($classMethod->stmts)) {
            return null;
        }

        foreach ($classMethod->stmts as $key => $stmt) {
            if ($stmt instanceof Return_ && $stmt->expr !== null) {
                if ($isArrayWrap && ! $stmt->expr instanceof Array_) {
                    $stmt->expr = new Array_([new ArrayItem($stmt->expr)]);
                }

                $classMethod->stmts[$key] = $stmt;
            }
        }

        return $classMethod;
    }
}

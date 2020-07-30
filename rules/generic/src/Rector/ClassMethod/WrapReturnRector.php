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
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\WrapReturnRector\WrapReturnRectorTest
 */
final class WrapReturnRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPE_TO_METHOD_TO_WRAP = 'type_to_method_to_wrap';

    /**
     * @var mixed[][]
     */
    private $typeToMethodToWrap = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Wrap return value of specific method', [
            new ConfiguredCodeSample(
                <<<'PHP'
final class SomeClass
{
    public function getItem()
    {
        return 1;
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    public function getItem()
    {
        return [1];
    }
}
PHP
                ,
                [
                    self::TYPE_TO_METHOD_TO_WRAP => [
                        'SomeClass' => [
                            'getItem' => 'array',
                        ],
                    ],
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
        foreach ($this->typeToMethodToWrap as $type => $methodToType) {
            if (! $this->isObjectType($node, $type)) {
                continue;
            }

            foreach ($methodToType as $method => $type) {
                if (! $this->isName($node, $method)) {
                    continue;
                }

                if (! $node->stmts) {
                    continue;
                }

                $this->wrap($node, $type);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->typeToMethodToWrap = $configuration[self::TYPE_TO_METHOD_TO_WRAP] ?? [];
    }

    private function wrap(ClassMethod $classMethod, string $type): void
    {
        if (! is_iterable($classMethod->stmts)) {
            return;
        }

        foreach ($classMethod->stmts as $i => $stmt) {
            if ($stmt instanceof Return_ && $stmt->expr !== null) {
                if ($type === 'array' && ! $stmt->expr instanceof Array_) {
                    $stmt->expr = new Array_([new ArrayItem($stmt->expr)]);
                }

                $classMethod->stmts[$i] = $stmt;
            }
        }
    }
}

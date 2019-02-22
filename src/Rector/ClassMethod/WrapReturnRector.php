<?php declare(strict_types=1);

namespace Rector\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class WrapReturnRector extends AbstractRector
{
    /**
     * @var mixed[][]
     */
    private $typeToMethodToWrap = [];

    /**
     * @param mixed[][] $typeToMethodToWrap
     */
    public function __construct(array $typeToMethodToWrap)
    {
        $this->typeToMethodToWrap = $typeToMethodToWrap;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Wrap return value of specific method', [
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
                    'SomeClass' => [
                        'getItem' => 'array',
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
            if (! $this->isType($node, $type)) {
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

    private function wrap(ClassMethod $classMethod, string $type): void
    {
        if (! is_iterable($classMethod->stmts)) {
            return;
        }

        foreach ($classMethod->stmts as $i => $stmt) {
            if ($stmt instanceof Return_ && $stmt->expr !== null) {
                if ($type === 'array') {
                    $stmt->expr = new Array_([new ArrayItem($stmt->expr)]);
                }

                $classMethod->stmts[$i] = $stmt;
            }
        }
    }
}

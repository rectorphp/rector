<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php80\Tests\Rector\Class_\TypedPropertyFromStrictConstructorRector\TypedPropertyFromStrictConstructorRectorTest
 */
final class TypedPropertyFromStrictConstructorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add typed properties based only on strict constructor types', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeObject
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeObject
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }

        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod === null) {
            return null;
        }

        // @todo resolve param to propery assign
        foreach ($constructClassMethod->getParams() as $parameter) {
        }

        // change the node

        return $node;
    }
}

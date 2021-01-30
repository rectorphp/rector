<?php

declare(strict_types=1);

namespace Rector\Symfony5\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#propertyaccess
 * @see \Rector\Symfony5\Tests\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector\PropertyAccessorCreationBooleanToFlagsRectorTest
 */
final class PropertyAccessorCreationBooleanToFlagsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes first argument of PropertyAccessor::__construct() to flags from boolean', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $propertyAccessor = new PropertyAccessor(true);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $propertyAccessor = new PropertyAccessor(PropertyAccessor::MAGIC_CALL | PropertyAccessor::MAGIC_GET | PropertyAccessor::MAGIC_SET);
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
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $isTrue = $this->valueResolver->isTrue($node->args[0]->value);
        $bitwiseOr = $this->prepareFlags($isTrue);
        $node->args[0] = $this->nodeFactory->createArg($bitwiseOr);

        return $node;
    }

    private function shouldSkip(New_ $new): bool
    {
        if (! $new->class instanceof Name) {
            return true;
        }

        if (! $this->isName($new->class, 'Symfony\Component\PropertyAccess\PropertyAccessor')) {
            return true;
        }
        return ! $this->valueResolver->isTrueOrFalse($new->args[0]->value);
    }

    private function prepareFlags(bool $currentValue): BitwiseOr
    {
        $classConstFetch = $this->nodeFactory->createClassConstFetch(
            'Symfony\Component\PropertyAccess\PropertyAccessor',
            'MAGIC_GET'
        );
        $magicSet = $this->nodeFactory->createClassConstFetch(
            'Symfony\Component\PropertyAccess\PropertyAccessor',
            'MAGIC_SET'
        );
        if (! $currentValue) {
            return new BitwiseOr($classConstFetch, $magicSet);
        }

        return new BitwiseOr(
            new BitwiseOr(
                $this->nodeFactory->createClassConstFetch(
                    'Symfony\Component\PropertyAccess\PropertyAccessor',
                    'MAGIC_CALL'
                ),
                $classConstFetch,
            ),
            $magicSet,
        );
    }
}

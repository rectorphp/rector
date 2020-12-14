<?php

declare(strict_types=1);

namespace Rector\Symfony5\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\New_;
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
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $propertyAccessor = new PropertyAccessor(true);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $propertyAccessor = new PropertyAccessor(PropertyAccessor::MAGIC_CALL | PropertyAccessor::MAGIC_GET | PropertyAccessor::MAGIC_SET);
    }
}
PHP
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

        $isTrue = $this->isTrue($node->args[0]->value);
        $flags = $this->prepareFlags($isTrue);
        $node->args[0] = $this->createArg($flags);

        return $node;
    }

    private function prepareFlags(bool $currentValue): BitwiseOr
    {
        $magicGet = $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_GET');
        $magicSet = $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_SET');
        if (!$currentValue) {
            return new BitwiseOr($magicGet, $magicSet);
        }

        return new BitwiseOr(
            new BitwiseOr(
                $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_CALL'),
                $magicGet,
            ),
            $magicSet,
        );
    }

    private function shouldSkip(New_ $new_): bool
    {
        if (! $new_->class instanceof Node\Name) {
            return true;
        }

        if (! $this->isName($new_->class, 'Symfony\Component\PropertyAccess\PropertyAccessor')) {
            return true;
        }

        if (! $this->isBool($new_->args[0]->value)) {
            return true;
        }

        return false;
    }
}

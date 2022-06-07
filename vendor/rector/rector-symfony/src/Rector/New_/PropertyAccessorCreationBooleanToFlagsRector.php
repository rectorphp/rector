<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#propertyaccess
 * @see \Rector\Symfony\Tests\Rector\New_\PropertyAccessorCreationBooleanToFlagsRector\PropertyAccessorCreationBooleanToFlagsRectorTest
 */
final class PropertyAccessorCreationBooleanToFlagsRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes first argument of PropertyAccessor::__construct() to flags from boolean', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $propertyAccessor = new PropertyAccessor(true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $propertyAccessor = new PropertyAccessor(PropertyAccessor::MAGIC_CALL | PropertyAccessor::MAGIC_GET | PropertyAccessor::MAGIC_SET);
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
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $firstArg = $node->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $isTrue = $this->valueResolver->isTrue($firstArg->value);
        $bitwiseOr = $this->prepareFlags($isTrue);
        $node->args[0] = $this->nodeFactory->createArg($bitwiseOr);
        return $node;
    }
    private function shouldSkip(New_ $new) : bool
    {
        if (!$new->class instanceof Name) {
            return \true;
        }
        if (!$this->isName($new->class, 'Symfony\\Component\\PropertyAccess\\PropertyAccessor')) {
            return \true;
        }
        if (!isset($new->args[0])) {
            return \true;
        }
        $firstArg = $new->args[0];
        if (!$firstArg instanceof Arg) {
            return \true;
        }
        return !$this->valueResolver->isTrueOrFalse($firstArg->value);
    }
    private function prepareFlags(bool $currentValue) : BitwiseOr
    {
        $magicGetClassConstFetch = $this->nodeFactory->createClassConstFetch('Symfony\\Component\\PropertyAccess\\PropertyAccessor', 'MAGIC_GET');
        $magicSetClassConstFetch = $this->nodeFactory->createClassConstFetch('Symfony\\Component\\PropertyAccess\\PropertyAccessor', 'MAGIC_SET');
        if (!$currentValue) {
            return new BitwiseOr($magicGetClassConstFetch, $magicSetClassConstFetch);
        }
        $magicCallClassConstFetch = $this->nodeFactory->createClassConstFetch('Symfony\\Component\\PropertyAccess\\PropertyAccessor', 'MAGIC_CALL');
        return new BitwiseOr(new BitwiseOr($magicCallClassConstFetch, $magicGetClassConstFetch), $magicSetClassConstFetch);
    }
}

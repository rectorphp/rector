<?php

declare(strict_types=1);

namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see http://php.net/manual/en/migration72.incompatible.php#migration72.incompatible.is_object-on-incomplete_class
 * @see https://3v4l.org/SpiE6
 *
 * @see \Rector\Php72\Tests\Rector\FuncCall\IsObjectOnIncompleteClassRector\IsObjectOnIncompleteClassRectorTest
 */
final class IsObjectOnIncompleteClassRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Incomplete class returns inverted bool on is_object()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$incompleteObject = new __PHP_Incomplete_Class;
$isObject = is_object($incompleteObject);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$incompleteObject = new __PHP_Incomplete_Class;
$isObject = ! is_object($incompleteObject);
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'is_object')) {
            return null;
        }

        $incompleteClassObjectType = new ObjectType('__PHP_Incomplete_Class');
        if (! $this->isObjectType($node->args[0]->value, $incompleteClassObjectType)) {
            return null;
        }

        if ($this->shouldSkip($node)) {
            return null;
        }

        $booleanNot = new BooleanNot($node);
        $node->setAttribute(AttributeKey::PARENT_NODE, $booleanNot);

        return $booleanNot;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        return $parentNode instanceof BooleanNot;
    }
}

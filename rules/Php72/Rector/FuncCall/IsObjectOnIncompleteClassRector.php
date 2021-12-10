<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog http://php.net/manual/en/migration72.incompatible.php#migration72.incompatible.is_object-on-incomplete_class https://3v4l.org/SpiE6
 *
 * @see \Rector\Tests\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector\IsObjectOnIncompleteClassRectorTest
 */
final class IsObjectOnIncompleteClassRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::INVERTED_BOOL_IS_OBJECT_INCOMPLETE_CLASS;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Incomplete class returns inverted bool on is_object()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$incompleteObject = new __PHP_Incomplete_Class;
$isObject = is_object($incompleteObject);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$incompleteObject = new __PHP_Incomplete_Class;
$isObject = ! is_object($incompleteObject);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node, 'is_object')) {
            return null;
        }
        $incompleteClassObjectType = new \PHPStan\Type\ObjectType('__PHP_Incomplete_Class');
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        if (!$this->isObjectType($node->args[0]->value, $incompleteClassObjectType)) {
            return null;
        }
        if ($this->shouldSkip($node)) {
            return null;
        }
        return new \PhpParser\Node\Expr\BooleanNot($node);
    }
    private function shouldSkip(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        $parentNode = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        return $parentNode instanceof \PhpParser\Node\Expr\BooleanNot;
    }
}

<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/deprecations_php_7_4 (not confirmed yet)
 * @changelog https://3v4l.org/69mpd
 * @see \Rector\Tests\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector\ArrayKeyExistsOnPropertyRectorTest
 */
final class ArrayKeyExistsOnPropertyRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ARRAY_KEY_EXISTS_TO_PROPERTY_EXISTS;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change array_key_exists() on property to property_exists()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
     public $value;
}
$someClass = new SomeClass;

array_key_exists('value', $someClass);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
     public $value;
}
$someClass = new SomeClass;

property_exists($someClass, 'value');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'array_key_exists')) {
            return null;
        }
        if (!isset($node->args[1])) {
            return null;
        }
        if (!$node->args[1] instanceof Arg) {
            return null;
        }
        $firstArgStaticType = $this->getType($node->args[1]->value);
        if (!$firstArgStaticType instanceof ObjectType) {
            return null;
        }
        $node->name = new Name('property_exists');
        $node->args = \array_reverse($node->args);
        return $node;
    }
}

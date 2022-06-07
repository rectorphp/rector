<?php

declare (strict_types=1);
namespace Rector\DowngradePhp55\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/class_name_scalars
 *
 * @see Rector\Tests\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector\DowngradeClassConstantToStringRectorTest
 */
final class DowngradeClassConstantToStringRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace <class>::class constant by string class names', [new CodeSample(<<<'CODE_SAMPLE'
class AnotherClass
{
}
class SomeClass
{
    public function run()
    {
        return \AnotherClass::class;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class AnotherClass
{
}
class SomeClass
{
    public function run()
    {
        return 'AnotherClass';
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
        return [ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if (\strtolower($node->name->name) !== 'class') {
            return null;
        }
        if (!$node->class instanceof Name) {
            return null;
        }
        $className = $node->class->toString();
        switch (\strtolower($className)) {
            case 'self':
                $func = 'get_class';
                break;
            case 'static':
                $func = 'get_called_class';
                break;
            case 'parent':
                $func = 'get_parent_class';
                break;
            default:
                $func = null;
                break;
        }
        if ($func !== null) {
            return $this->nodeFactory->createFuncCall($func);
        }
        return new String_($className);
    }
}

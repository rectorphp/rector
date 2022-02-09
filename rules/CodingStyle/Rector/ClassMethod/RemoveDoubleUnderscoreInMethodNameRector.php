<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use RectorPrefix20220209\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\CodingStyle\ValueObject\ObjectMagicMethods;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StringUtils;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector\RemoveDoubleUnderscoreInMethodNameRectorTest
 */
final class RemoveDoubleUnderscoreInMethodNameRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/oRrhDJ/3
     */
    private const DOUBLE_UNDERSCORE_START_REGEX = '#^__(.+)#';
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Non-magic PHP object methods cannot start with "__"', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __getName($anotherObject)
    {
        $anotherObject->__getSurname();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function getName($anotherObject)
    {
        $anotherObject->getSurname();
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }
        if (\in_array($methodName, \Rector\CodingStyle\ValueObject\ObjectMagicMethods::METHOD_NAMES, \true)) {
            return null;
        }
        if (!\Rector\Core\Util\StringUtils::isMatch($methodName, self::DOUBLE_UNDERSCORE_START_REGEX)) {
            return null;
        }
        $newName = \RectorPrefix20220209\Nette\Utils\Strings::substring($methodName, 2);
        if (\is_numeric($newName[0])) {
            return null;
        }
        $node->name = new \PhpParser\Node\Identifier($newName);
        return $node;
    }
}

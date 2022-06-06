<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodingStyle\Rector\ClassMethod;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\Rector\CodingStyle\ValueObject\ObjectMagicMethods;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\Util\StringUtils;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector\RemoveDoubleUnderscoreInMethodNameRectorTest
 */
final class RemoveDoubleUnderscoreInMethodNameRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/oRrhDJ/3
     */
    private const DOUBLE_UNDERSCORE_START_REGEX = '#^__(.+)#';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Non-magic PHP object methods cannot start with "__"', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class, MethodCall::class, StaticCall::class];
    }
    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }
        if (\in_array($methodName, ObjectMagicMethods::METHOD_NAMES, \true)) {
            return null;
        }
        if (!StringUtils::isMatch($methodName, self::DOUBLE_UNDERSCORE_START_REGEX)) {
            return null;
        }
        $newName = Strings::substring($methodName, 2);
        if (\is_numeric($newName[0])) {
            return null;
        }
        $node->name = new Identifier($newName);
        return $node;
    }
}

<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v6;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79316-DeprecateArrayUtilityinArray.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\ArrayUtilityInArrayToFuncInArrayRector\ArrayUtilityInArrayToFuncInArrayRectorTest
 */
final class ArrayUtilityInArrayToFuncInArrayRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Utility\\ArrayUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'inArray')) {
            return null;
        }
        return $this->nodeFactory->createFuncCall('in_array', [$node->args[1], $node->args[0], $this->nodeFactory->createTrue()]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Method inArray from ArrayUtility to in_array', [new CodeSample('ArrayUtility::inArray()', 'in_array')]);
    }
}

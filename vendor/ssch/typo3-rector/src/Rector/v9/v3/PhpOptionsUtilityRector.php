<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v3;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220606\TYPO3\CMS\Core\Utility\PhpOptionsUtility;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.3/Deprecation-85102-PhpOptionsUtility.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v3\PhpOptionsUtilityRector\PhpOptionsUtilityRectorTest
 */
final class PhpOptionsUtilityRector extends AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Utility\\PhpOptionsUtility'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['isSessionAutoStartEnabled', 'getIniValueBoolean'])) {
            return null;
        }
        $configOption = 'session.auto_start';
        if ($this->isName($node->name, 'getIniValueBoolean')) {
            $configOption = $this->valueResolver->getValue($node->args[0]->value);
        }
        return $this->nodeFactory->createFuncCall('filter_var', [$this->nodeFactory->createFuncCall('ini_get', [$configOption]), new ConstFetch(new Name('FILTER_VALIDATE_BOOLEAN')), new Array_([new ArrayItem(new ConstFetch(new Name('FILTER_REQUIRE_SCALAR'))), new ArrayItem(new ConstFetch(new Name('FILTER_NULL_ON_FAILURE')))])]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor methods from PhpOptionsUtility', [new CodeSample('PhpOptionsUtility::isSessionAutoStartEnabled()', "filter_var(ini_get('session.auto_start'), FILTER_VALIDATE_BOOLEAN, [FILTER_REQUIRE_SCALAR, FILTER_NULL_ON_FAILURE])")]);
    }
}

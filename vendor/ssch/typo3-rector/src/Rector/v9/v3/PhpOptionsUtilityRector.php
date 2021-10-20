<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v3;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\TYPO3\CMS\Core\Utility\PhpOptionsUtility;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.3/Deprecation-85102-PhpOptionsUtility.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v3\PhpOptionsUtilityRector\PhpOptionsUtilityRectorTest
 */
final class PhpOptionsUtilityRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\PhpOptionsUtility'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['isSessionAutoStartEnabled', 'getIniValueBoolean'])) {
            return null;
        }
        $configOption = 'session.auto_start';
        if ($this->isName($node->name, 'getIniValueBoolean')) {
            $configOption = $this->valueResolver->getValue($node->args[0]->value);
        }
        return $this->nodeFactory->createFuncCall('filter_var', [$this->nodeFactory->createFuncCall('ini_get', [$configOption]), new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('FILTER_VALIDATE_BOOLEAN')), new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('FILTER_REQUIRE_SCALAR'))), new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('FILTER_NULL_ON_FAILURE')))])]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor methods from PhpOptionsUtility', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('PhpOptionsUtility::isSessionAutoStartEnabled()', "filter_var(ini_get('session.auto_start'), FILTER_VALIDATE_BOOLEAN, [FILTER_REQUIRE_SCALAR, FILTER_NULL_ON_FAILURE])")]);
    }
}

<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85759-GeneralUtilitygetHostName.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\GeneralUtilityGetHostNameToGetIndpEnvRector\GeneralUtilityGetHostNameToGetIndpEnvRectorTest
 */
final class GeneralUtilityGetHostNameToGetIndpEnvRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getHostname')) {
            return null;
        }
        $node->name = new \PhpParser\Node\Identifier('getIndpEnv');
        $node->args = $this->nodeFactory->createArgs(['HTTP_HOST']);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition("Migrating method call GeneralUtility::getHostname() to GeneralUtility::getIndpEnv('HTTP_HOST')", [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
\TYPO3\CMS\Core\Utility\GeneralUtility::getHostname();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST')
CODE_SAMPLE
)]);
    }
}

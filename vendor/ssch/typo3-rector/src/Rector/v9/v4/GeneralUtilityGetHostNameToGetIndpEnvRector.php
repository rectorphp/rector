<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v4;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85759-GeneralUtilitygetHostName.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\GeneralUtilityGetHostNameToGetIndpEnvRector\GeneralUtilityGetHostNameToGetIndpEnvRectorTest
 */
final class GeneralUtilityGetHostNameToGetIndpEnvRector extends AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getHostname')) {
            return null;
        }
        $node->name = new Identifier('getIndpEnv');
        $node->args = $this->nodeFactory->createArgs(['HTTP_HOST']);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition("Migrating method call GeneralUtility::getHostname() to GeneralUtility::getIndpEnv('HTTP_HOST')", [new CodeSample(<<<'CODE_SAMPLE'
\TYPO3\CMS\Core\Utility\GeneralUtility::getHostname();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST')
CODE_SAMPLE
)]);
    }
}

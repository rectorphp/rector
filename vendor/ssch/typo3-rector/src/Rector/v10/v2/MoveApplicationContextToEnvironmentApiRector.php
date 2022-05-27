<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v2;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.2/Deprecation-89631-UseEnvironmentAPIToFetchApplicationContext.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v2\MoveApplicationContextToEnvironmentApiRector\MoveApplicationContextToEnvironmentApiRectorTest
 */
final class MoveApplicationContextToEnvironmentApiRector extends AbstractRector
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
        if (!$this->isName($node->name, 'getApplicationContext')) {
            return null;
        }
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'getContext', []);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use Environment API to fetch application context', [new CodeSample(<<<'CODE_SAMPLE'
GeneralUtility::getApplicationContext();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Environment::getContext();
CODE_SAMPLE
)]);
    }
}

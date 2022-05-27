<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.0/Important-92736-ReturnTimestampAsIntegerInDateTimeAspect.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v0\DateTimeAspectInsteadOfGlobalsExecTimeRector\DateTimeAspectInsteadOfGlobalsExecTimeRectorTest
 */
final class DateTimeAspectInsteadOfGlobalsExecTimeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ArrayDimFetch::class];
    }
    /**
     * @param ArrayDimFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->typo3NodeResolver->isTypo3Globals($node, [Typo3NodeResolver::EXEC_TIME, Typo3NodeResolver::SIM_ACCESS_TIME, Typo3NodeResolver::SIM_EXEC_TIME, Typo3NodeResolver::ACCESS_TIME])) {
            return null;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Context\\Context')]), 'getPropertyFromAspect', ['date', 'timestamp']);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use DateTimeAspect instead of superglobals like $GLOBALS[\'EXEC_TIME\']', [new CodeSample(<<<'CODE_SAMPLE'
$currentTimestamp = $GLOBALS['EXEC_TIME'];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$currentTimestamp = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp');
CODE_SAMPLE
)]);
    }
}

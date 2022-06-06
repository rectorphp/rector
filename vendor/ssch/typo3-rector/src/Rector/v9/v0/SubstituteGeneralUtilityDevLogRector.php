<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\MagicConst\Class_;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\OldSeverityToLogLevelMapper;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-52694-DeprecatedGeneralUtilitydevLog.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\SubstituteGeneralUtilityDevLogRector\SubstituteGeneralUtilityDevLogRectorTest
 */
final class SubstituteGeneralUtilityDevLogRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\OldSeverityToLogLevelMapper
     */
    private $oldSeverityToLogLevelMapper;
    public function __construct(OldSeverityToLogLevelMapper $oldSeverityToLogLevelMapper)
    {
        $this->oldSeverityToLogLevelMapper = $oldSeverityToLogLevelMapper;
    }
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
        if (!$this->isName($node->name, 'devLog')) {
            return null;
        }
        $makeInstanceCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Log\\LogManager')]);
        $loggerCall = $this->nodeFactory->createMethodCall($makeInstanceCall, 'getLogger', [new Class_()]);
        $args = [];
        $severity = $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Log\\LogLevel', 'INFO');
        if (isset($node->args[2]) && ($severityValue = $this->valueResolver->getValue($node->args[2]->value))) {
            $severity = $this->oldSeverityToLogLevelMapper->mapSeverityToLogLevel($severityValue);
        }
        $args[] = $severity;
        $args[] = $node->args[0] ?? $this->nodeFactory->createArg(new String_(''));
        $args[] = $node->args[3] ?? $this->nodeFactory->createArg(new String_(''));
        return $this->nodeFactory->createMethodCall($loggerCall, 'log', $args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Substitute GeneralUtility::devLog() to Logging API', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
GeneralUtility::devLog('message', 'foo', 0, $data);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__)->log(LogLevel::INFO, 'message', $data);
CODE_SAMPLE
)]);
    }
}

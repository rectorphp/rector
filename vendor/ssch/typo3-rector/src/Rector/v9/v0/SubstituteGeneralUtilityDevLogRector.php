<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\MagicConst\Class_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\OldSeverityToLogLevelMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-52694-DeprecatedGeneralUtilitydevLog.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\SubstituteGeneralUtilityDevLogRector\SubstituteGeneralUtilityDevLogRectorTest
 */
final class SubstituteGeneralUtilityDevLogRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Ssch\TYPO3Rector\Helper\OldSeverityToLogLevelMapper
     */
    private $oldSeverityToLogLevelMapper;
    public function __construct(\Ssch\TYPO3Rector\Helper\OldSeverityToLogLevelMapper $oldSeverityToLogLevelMapper)
    {
        $this->oldSeverityToLogLevelMapper = $oldSeverityToLogLevelMapper;
    }
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
        if (!$this->isName($node->name, 'devLog')) {
            return null;
        }
        $makeInstanceCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Log\\LogManager')]);
        $loggerCall = $this->nodeFactory->createMethodCall($makeInstanceCall, 'getLogger', [new \PhpParser\Node\Scalar\MagicConst\Class_()]);
        $args = [];
        $severity = $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Core\\Log\\LogLevel', 'INFO');
        if (isset($node->args[2]) && ($severityValue = $this->valueResolver->getValue($node->args[2]->value))) {
            $severity = $this->oldSeverityToLogLevelMapper->mapSeverityToLogLevel($severityValue);
        }
        $args[] = $severity;
        $args[] = $node->args[0] ?? $this->nodeFactory->createArg(new \PhpParser\Node\Scalar\String_(''));
        $args[] = $node->args[3] ?? $this->nodeFactory->createArg(new \PhpParser\Node\Scalar\String_(''));
        return $this->nodeFactory->createMethodCall($loggerCall, 'log', $args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Substitute GeneralUtility::devLog() to Logging API', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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

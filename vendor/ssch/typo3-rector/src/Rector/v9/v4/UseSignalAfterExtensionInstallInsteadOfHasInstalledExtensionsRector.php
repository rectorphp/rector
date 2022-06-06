<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v4;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\NodeAnalyzer\ClassConstAnalyzer;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85462-SignalHasInstalledExtensions.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\UseSignalAfterExtensionInstallInsteadOfHasInstalledExtensionsRector\UseSignalAfterExtensionInstallInsteadOfHasInstalledExtensionsRectorTest
 */
final class UseSignalAfterExtensionInstallInsteadOfHasInstalledExtensionsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\NodeAnalyzer\ClassConstAnalyzer
     */
    private $classConstAnalyzer;
    public function __construct(ClassConstAnalyzer $classConstAnalyzer)
    {
        $this->classConstAnalyzer = $classConstAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher'))) {
            return null;
        }
        if (!$this->isName($node->name, 'connect')) {
            return null;
        }
        if (!$this->classConstAnalyzer->isClassConstReference($node->args[0]->value, 'TYPO3\\CMS\\Extensionmanager\\Service\\ExtensionManagementService')) {
            return null;
        }
        if (!$this->valueResolver->isValue($node->args[1]->value, 'hasInstalledExtensions')) {
            return null;
        }
        $node->args[0]->value = $this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Extensionmanager\\Utility\\InstallUtility');
        $node->args[1] = $this->nodeFactory->createArg('afterExtensionInstall');
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use the signal afterExtensionInstall of class InstallUtility', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extensionmanager\Service\ExtensionManagementService;

$signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
$signalSlotDispatcher->connect(
    ExtensionManagementService::class,
    'hasInstalledExtensions',
    \stdClass::class,
    'foo'
);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

$signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
$signalSlotDispatcher->connect(
    InstallUtility::class,
    'afterExtensionInstall',
    \stdClass::class,
    'foo'
);
CODE_SAMPLE
)]);
    }
}

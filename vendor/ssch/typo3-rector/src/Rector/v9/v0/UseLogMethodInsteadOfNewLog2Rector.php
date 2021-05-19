<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.0/Deprecation-83121-LoggingMethodDataHandler-newlog2.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v0\UseLogMethodInsteadOfNewLog2Rector\UseLogMethodInsteadOfNewLog2RectorTest
 */
final class UseLogMethodInsteadOfNewLog2Rector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const PID = 'pid';
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\DataHandling\\DataHandler'))) {
            return null;
        }
        if (!$this->isName($node->name, 'newlog2')) {
            return null;
        }
        if (!isset($node->args[3]) || isset($node->args[3]) && $this->valueResolver->isNull($node->args[3]->value)) {
            $propArrayNode = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable('propArr'), $this->nodeFactory->createMethodCall($node->var, 'getRecordProperties', [$node->args[1], $node->args[2]]));
            $this->addNodeBeforeNode($propArrayNode, $node);
            $pidNode = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::PID), new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('propArr'), new \PhpParser\Node\Scalar\String_(self::PID))));
            $this->addNodeBeforeNode($pidNode, $node);
            $this->addNodeBeforeNode(new \PhpParser\Node\Stmt\Nop(), $node);
        }
        $node->name = new \PhpParser\Node\Identifier('log');
        $node->args = $this->nodeFactory->createArgs([$node->args[1], $node->args[2], new \PhpParser\Node\Scalar\LNumber(0), new \PhpParser\Node\Scalar\LNumber(0), $node->args[4] ?? new \PhpParser\Node\Scalar\LNumber(0), $node->args[0], new \PhpParser\Node\Scalar\LNumber(-1), new \PhpParser\Node\Expr\Array_(), $this->nodeFactory->createMethodCall($node->var, 'eventPid', [$node->args[1], $node->args[2], isset($node->args[3]) && !$this->valueResolver->isNull($node->args[3]->value) ? $node->args[3] : new \PhpParser\Node\Expr\Variable(self::PID)])]);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use log method instead of newlog2 from class DataHandler', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
$logEntryUid1 = $dataHandler->newlog2('Foo', 'pages', 1, null, 0);
$logEntryUid2 = $dataHandler->newlog2('Foo', 'tt_content', 1, 2, 1);
$logEntryUid3 = $dataHandler->newlog2('Foo', 'tt_content', 1);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
$propArr = $dataHandler->getRecordProperties('pages', 1);
$pid = $propArr['pid'];

$logEntryUid1 = $dataHandler->log('pages', 1, 0, 0, 0, 'Foo', -1, [], $dataHandler->eventPid('pages', 1, $pid));
$logEntryUid2 = $dataHandler->log('tt_content', 1, 0, 0, 1, 'Foo', -1, [], $dataHandler->eventPid('tt_content', 1, 2));
$propArr = $dataHandler->getRecordProperties('tt_content', 1);
$pid = $propArr['pid'];

$logEntryUid3 = $dataHandler->log('tt_content', 1, 0, 0, 0, 'Foo', -1, [], $dataHandler->eventPid('tt_content', 1, $pid));
CODE_SAMPLE
)]);
    }
}

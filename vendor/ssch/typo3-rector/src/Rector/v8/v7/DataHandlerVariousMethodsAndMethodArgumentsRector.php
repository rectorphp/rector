<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v7;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80513-DataHandlerVariousMethodsAndMethodArguments.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\DataHandlerVariousMethodsAndMethodArgumentsRector\DataHandlerVariousMethodsAndMethodArgumentsRectorTest
 */
final class DataHandlerVariousMethodsAndMethodArgumentsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\DataHandling\\DataHandler'))) {
            return null;
        }
        if ($this->isName($node->name, 'destPathFromUploadFolder')) {
            /** @var Arg[] $args */
            $args = $node->args;
            $firstArgument = \array_shift($args);
            if (!$firstArgument instanceof \PhpParser\Node\Arg) {
                return null;
            }
            return new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('PATH_site')), $firstArgument->value);
        }
        if ($this->isName($node->name, 'extFileFunctions') && 4 === \count($node->args)) {
            $this->removeNode($node->args[3]);
            return $node;
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove CharsetConvertParameters', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
$dest = $dataHandler->destPathFromUploadFolder('uploadFolder');
$dataHandler->extFileFunctions('table', 'field', 'theField', 'deleteAll');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$dataHandler = GeneralUtility::makeInstance(DataHandler::class);
$dest = PATH_site . 'uploadFolder';
$dataHandler->extFileFunctions('table', 'field', 'theField');
CODE_SAMPLE
)]);
    }
}

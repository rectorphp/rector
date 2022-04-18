<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v7;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220418\TYPO3\CMS\Core\Imaging\GraphicalFunctions;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80514-GraphicalFunctions-tempPathAndCreateTempSubDir.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRector\RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRectorTest
 */
final class RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const CREATE_TEMP_SUB_DIR = 'createTempSubDir';
    /**
     * @var string
     */
    private const TEMP_PATH = 'tempPath';
    /**
     * @var string
     */
    private const TMP_PATH = 'tmpPath';
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param MethodCall|PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->refactorMethodCall($node);
        }
        return $this->refactorPropertyFetch($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor tempPath() and createTempSubDir on GraphicalFunctions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$graphicalFunctions = GeneralUtility::makeInstance(GraphicalFunctions::class);
$graphicalFunctions->createTempSubDir('var/transient/');
return $graphicalFunctions->tempPath . 'var/transient/';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$graphicalFunctions = GeneralUtility::makeInstance(GraphicalFunctions::class);
GeneralUtility::mkdir_deep(PATH_site . 'typo3temp/var/transient/');
return 'typo3temp/' . 'var/transient/';
CODE_SAMPLE
)]);
    }
    private function refactorMethodCall(\PhpParser\Node\Expr\MethodCall $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions'))) {
            return null;
        }
        if (!$this->isName($node->name, self::CREATE_TEMP_SUB_DIR)) {
            return null;
        }
        if ([] === $node->args) {
            return null;
        }
        $argumentValue = $this->valueResolver->getValue($node->args[0]->value);
        if (null === $argumentValue) {
            return null;
        }
        $anonymousFunction = new \PhpParser\Node\Expr\Closure();
        $anonymousFunction->params = [new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable(self::TEMP_PATH)), new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable('dirName'))];
        $ifIsPartOfStrMethodCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'isFirstPartOfStr', [new \PhpParser\Node\Expr\Variable(self::TEMP_PATH), new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('PATH_site'))]);
        $ifIsPartOfStr = new \PhpParser\Node\Stmt\If_($ifIsPartOfStrMethodCall);
        $ifIsPartOfStr->stmts[] = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::TMP_PATH), new \PhpParser\Node\Expr\Variable(self::TEMP_PATH)));
        $ifIsPartOfStr->else = new \PhpParser\Node\Stmt\Else_();
        $ifIsPartOfStr->else->stmts[] = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::TMP_PATH), new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('PATH_site')), new \PhpParser\Node\Expr\Variable(self::TEMP_PATH))));
        $anonymousFunction->stmts[] = $ifIsPartOfStr;
        $concatTempPathAndDirName = $this->nodeFactory->createConcat([new \PhpParser\Node\Expr\Variable(self::TMP_PATH), new \PhpParser\Node\Expr\Variable('dirName')]);
        if (!$concatTempPathAndDirName instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            return null;
        }
        $isDirFunc = new \PhpParser\Node\Expr\ErrorSuppress($this->nodeFactory->createFuncCall('is_dir', [$concatTempPathAndDirName]));
        $ifIsNotDir = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BooleanNot($isDirFunc));
        $ifIsNotDir->stmts[] = new \PhpParser\Node\Stmt\Expression($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'mkdir_deep', [$concatTempPathAndDirName]));
        $ifIsNotDir->stmts[] = new \PhpParser\Node\Stmt\Return_($isDirFunc);
        $anonymousFunction->stmts[] = $ifIsNotDir;
        $anonymousFunction->stmts[] = new \PhpParser\Node\Stmt\Return_($this->nodeFactory->createFalse());
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::CREATE_TEMP_SUB_DIR), $anonymousFunction)), $parentNode);
        // Could not figure how to call the closure like that $function();
        return $this->nodeFactory->createFuncCall('call_user_func', [new \PhpParser\Node\Expr\Variable(self::CREATE_TEMP_SUB_DIR), new \PhpParser\Node\Scalar\String_('typo3temp'), $node->args[0]->value]);
    }
    private function refactorPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions'))) {
            return null;
        }
        if (!$this->isName($node->name, self::TEMP_PATH)) {
            return null;
        }
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        // Check if we have an assigment to the property, if so do not change it
        if ($parentNode instanceof \PhpParser\Node\Expr\Assign && $parentNode->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return null;
        }
        return new \PhpParser\Node\Scalar\String_('typo3temp/');
    }
}

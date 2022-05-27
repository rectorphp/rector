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
use RectorPrefix20220527\TYPO3\CMS\Core\Imaging\GraphicalFunctions;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80514-GraphicalFunctions-tempPathAndCreateTempSubDir.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRector\RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRectorTest
 */
final class RefactorGraphicalFunctionsTempPathAndCreateTemSubDirRector extends AbstractRector
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
        return [MethodCall::class, PropertyFetch::class];
    }
    /**
     * @param MethodCall|PropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof MethodCall) {
            return $this->refactorMethodCall($node);
        }
        return $this->refactorPropertyFetch($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor tempPath() and createTempSubDir on GraphicalFunctions', [new CodeSample(<<<'CODE_SAMPLE'
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
    private function refactorMethodCall(MethodCall $methodCall) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new ObjectType('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions'))) {
            return null;
        }
        if (!$this->isName($methodCall->name, self::CREATE_TEMP_SUB_DIR)) {
            return null;
        }
        if ([] === $methodCall->args) {
            return null;
        }
        $argumentValue = $this->valueResolver->getValue($methodCall->args[0]->value);
        if (null === $argumentValue) {
            return null;
        }
        $anonymousFunction = new Closure();
        $anonymousFunction->params = [new Param(new Variable(self::TEMP_PATH)), new Param(new Variable('dirName'))];
        $ifIsPartOfStrMethodCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'isFirstPartOfStr', [new Variable(self::TEMP_PATH), new ConstFetch(new Name('PATH_site'))]);
        $ifIsPartOfStr = new If_($ifIsPartOfStrMethodCall);
        $ifIsPartOfStr->stmts[] = new Expression(new Assign(new Variable(self::TMP_PATH), new Variable(self::TEMP_PATH)));
        $ifIsPartOfStr->else = new Else_();
        $ifIsPartOfStr->else->stmts[] = new Expression(new Assign(new Variable(self::TMP_PATH), new Concat(new ConstFetch(new Name('PATH_site')), new Variable(self::TEMP_PATH))));
        $anonymousFunction->stmts[] = $ifIsPartOfStr;
        $concatTempPathAndDirName = $this->nodeFactory->createConcat([new Variable(self::TMP_PATH), new Variable('dirName')]);
        if (!$concatTempPathAndDirName instanceof Concat) {
            return null;
        }
        $isDirFunc = new ErrorSuppress($this->nodeFactory->createFuncCall('is_dir', [$concatTempPathAndDirName]));
        $ifIsNotDir = new If_(new BooleanNot($isDirFunc));
        $ifIsNotDir->stmts[] = new Expression($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'mkdir_deep', [$concatTempPathAndDirName]));
        $ifIsNotDir->stmts[] = new Return_($isDirFunc);
        $anonymousFunction->stmts[] = $ifIsNotDir;
        $anonymousFunction->stmts[] = new Return_($this->nodeFactory->createFalse());
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression(new Assign(new Variable(self::CREATE_TEMP_SUB_DIR), $anonymousFunction)), $parentNode);
        // Could not figure how to call the closure like that $function();
        return $this->nodeFactory->createFuncCall('call_user_func', [new Variable(self::CREATE_TEMP_SUB_DIR), new String_('typo3temp'), $methodCall->args[0]->value]);
    }
    private function refactorPropertyFetch(PropertyFetch $propertyFetch) : ?Node
    {
        if (!$this->isObjectType($propertyFetch->var, new ObjectType('TYPO3\\CMS\\Core\\Imaging\\GraphicalFunctions'))) {
            return null;
        }
        if (!$this->isName($propertyFetch->name, self::TEMP_PATH)) {
            return null;
        }
        $parentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);
        // Check if we have an assigment to the property, if so do not change it
        if ($parentNode instanceof Assign && $parentNode->var instanceof PropertyFetch) {
            return null;
        }
        return new String_('typo3temp/');
    }
}

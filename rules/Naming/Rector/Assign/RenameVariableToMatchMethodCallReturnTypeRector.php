<?php

declare (strict_types=1);
namespace Rector\Naming\Rector\Assign;

use RectorPrefix202312\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Matcher\VariableAndCallAssignMatcher;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\NamingConvention\NamingConventionAnalyzer;
use Rector\Naming\PhpDoc\VarTagValueNodeRenamer;
use Rector\Naming\ValueObject\VariableAndCallAssign;
use Rector\Naming\VariableRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector\RenameVariableToMatchMethodCallReturnTypeRectorTest
 */
final class RenameVariableToMatchMethodCallReturnTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Guard\BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\ExpectedNameResolver
     */
    private $expectedNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\NamingConvention\NamingConventionAnalyzer
     */
    private $namingConventionAnalyzer;
    /**
     * @readonly
     * @var \Rector\Naming\PhpDoc\VarTagValueNodeRenamer
     */
    private $varTagValueNodeRenamer;
    /**
     * @readonly
     * @var \Rector\Naming\Matcher\VariableAndCallAssignMatcher
     */
    private $variableAndCallAssignMatcher;
    /**
     * @readonly
     * @var \Rector\Naming\VariableRenamer
     */
    private $variableRenamer;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var string
     * @see https://regex101.com/r/JG5w9j/1
     */
    private const OR_BETWEEN_WORDS_REGEX = '#[a-z]Or[A-Z]#';
    /**
     * @var string
     * @see https://regex101.com/r/TV8YXZ/1
     */
    private const VALID_VARIABLE_NAME_REGEX = '#^[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*$#';
    public function __construct(BreakingVariableRenameGuard $breakingVariableRenameGuard, ExpectedNameResolver $expectedNameResolver, NamingConventionAnalyzer $namingConventionAnalyzer, VarTagValueNodeRenamer $varTagValueNodeRenamer, VariableAndCallAssignMatcher $variableAndCallAssignMatcher, VariableRenamer $variableRenamer, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->namingConventionAnalyzer = $namingConventionAnalyzer;
        $this->varTagValueNodeRenamer = $varTagValueNodeRenamer;
        $this->variableAndCallAssignMatcher = $variableAndCallAssignMatcher;
        $this->variableRenamer = $variableRenamer;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename variable to match method return type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $a = $this->getRunner();
    }

    public function getRunner(): Runner
    {
        return new Runner();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $runner = $this->getRunner();
    }

    public function getRunner(): Runner
    {
        return new Runner();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Closure::class, Function_::class];
    }
    /**
     * @param ClassMethod|Closure|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            $variableAndCallAssign = $this->variableAndCallAssignMatcher->match($assign, $node);
            if (!$variableAndCallAssign instanceof VariableAndCallAssign) {
                return null;
            }
            $call = $variableAndCallAssign->getCall();
            $expectedName = $this->expectedNameResolver->resolveForCall($call);
            if ($expectedName === null) {
                continue;
            }
            if ($this->isName($assign->var, $expectedName)) {
                continue;
            }
            if ($this->shouldSkip($variableAndCallAssign, $expectedName)) {
                continue;
            }
            $this->renameVariable($variableAndCallAssign, $expectedName, $stmt);
            return $node;
        }
        return null;
    }
    private function shouldSkip(VariableAndCallAssign $variableAndCallAssign, string $expectedName) : bool
    {
        if (Strings::match($expectedName, self::VALID_VARIABLE_NAME_REGEX) === null) {
            return \true;
        }
        if ($this->namingConventionAnalyzer->isCallMatchingVariableName($variableAndCallAssign->getCall(), $variableAndCallAssign->getVariableName(), $expectedName)) {
            return \true;
        }
        $isUnionName = Strings::match($variableAndCallAssign->getVariableName(), self::OR_BETWEEN_WORDS_REGEX);
        if ($isUnionName !== null) {
            return \true;
        }
        return $this->breakingVariableRenameGuard->shouldSkipVariable($variableAndCallAssign->getVariableName(), $expectedName, $variableAndCallAssign->getFunctionLike(), $variableAndCallAssign->getVariable());
    }
    private function renameVariable(VariableAndCallAssign $variableAndCallAssign, string $expectedName, Expression $expression) : void
    {
        $this->variableRenamer->renameVariableInFunctionLike($variableAndCallAssign->getFunctionLike(), $variableAndCallAssign->getVariableName(), $expectedName, $variableAndCallAssign->getAssign());
        $assignPhpDocInfo = $this->phpDocInfoFactory->createFromNode($expression);
        if (!$assignPhpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $this->varTagValueNodeRenamer->renameAssignVarTagVariableName($assignPhpDocInfo, $variableAndCallAssign->getVariableName(), $expectedName);
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($expression);
    }
}

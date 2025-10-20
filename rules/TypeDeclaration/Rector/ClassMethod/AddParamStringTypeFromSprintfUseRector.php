<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\Guard\ParamTypeAddGuard;
use Rector\TypeDeclaration\NodeAnalyzer\VariableInSprintfMaskMatcher;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamStringTypeFromSprintfUseRector\AddParamStringTypeFromSprintfUseRectorTest
 */
final class AddParamStringTypeFromSprintfUseRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VariableInSprintfMaskMatcher $variableInSprintfMaskMatcher;
    /**
     * @readonly
     */
    private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     */
    private ParamTypeAddGuard $paramTypeAddGuard;
    public function __construct(VariableInSprintfMaskMatcher $variableInSprintfMaskMatcher, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, ParamTypeAddGuard $paramTypeAddGuard)
    {
        $this->variableInSprintfMaskMatcher = $variableInSprintfMaskMatcher;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->paramTypeAddGuard = $paramTypeAddGuard;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add string type to parameters used in sprintf calls', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function formatMessage($name)
    {
        return sprintf('My name is %s', $name);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function formatMessage(string $name)
    {
        return sprintf('My name is %s', $name);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class];
    }
    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof ClassMethod && $node->stmts === null) {
            return null;
        }
        if ($node->getParams() === []) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getParams() as $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            // skip non string default value
            if ($param->default instanceof Expr && !$param->default instanceof String_) {
                continue;
            }
            if (!$this->paramTypeAddGuard->isLegal($param, $node)) {
                continue;
            }
            $variableName = $this->getName($param);
            if (!$this->variableInSprintfMaskMatcher->matchMask($node, $variableName, '%s')) {
                continue;
            }
            $param->type = new Identifier('string');
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}

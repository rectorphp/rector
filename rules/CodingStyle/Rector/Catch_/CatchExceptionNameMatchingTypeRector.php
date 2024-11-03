<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Catch_;

use RectorPrefix202411\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\CatchExceptionNameMatchingTypeRectorTest
 */
final class CatchExceptionNameMatchingTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var string
     * @see https://regex101.com/r/xmfMAX/1
     */
    private const STARTS_WITH_ABBREVIATION_REGEX = '#^([A-Za-z]+?)([A-Z]{1}[a-z]{1})([A-Za-z]*)#';
    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Type and name of catch exception should match', [new CodeSample(<<<'CODE_SAMPLE'
try {
    // ...
} catch (SomeException $typoException) {
    $typoException->getMessage();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
try {
    // ...
} catch (SomeException $someException) {
    $someException->getMessage();
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class, Closure::class, FileWithoutNamespace::class, Namespace_::class];
    }
    /**
     * @param ClassMethod|Function_|Closure|FileWithoutNamespace|Namespace_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if ($this->shouldSkip($stmt)) {
                continue;
            }
            // variable defined first only resolvable by Scope pulled from Stmt
            $scope = $stmt->getAttribute(AttributeKey::SCOPE);
            if (!$scope instanceof Scope) {
                continue;
            }
            /** @var TryCatch $stmt */
            $catch = $stmt->catches[0];
            /** @var Variable $catchVar */
            $catchVar = $catch->var;
            /** @var string $oldVariableName */
            $oldVariableName = (string) $this->getName($catchVar);
            $typeShortName = $this->resolveVariableName($catch->types[0]);
            $newVariableName = $this->resolveNewVariableName($typeShortName);
            $objectType = new ObjectType($newVariableName);
            $newVariableName = $this->propertyNaming->fqnToVariableName($objectType);
            if ($oldVariableName === $newVariableName) {
                continue;
            }
            $isFoundInPrevious = $scope->hasVariableType($newVariableName)->yes();
            if ($isFoundInPrevious) {
                return null;
            }
            $catch->var = new Variable($newVariableName);
            $this->renameVariableInStmts($catch, $oldVariableName, $newVariableName, $key, $node->stmts, $node->stmts[$key + 1] ?? null);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function resolveNewVariableName(string $typeShortName) : string
    {
        return Strings::replace(\lcfirst($typeShortName), self::STARTS_WITH_ABBREVIATION_REGEX, static function (array $matches) : string {
            $output = isset($matches[1]) ? \strtolower((string) $matches[1]) : '';
            $output .= $matches[2] ?? '';
            return $output . ($matches[3] ?? '');
        });
    }
    private function shouldSkip(Stmt $stmt) : bool
    {
        if (!$stmt instanceof TryCatch) {
            return \true;
        }
        if (\count($stmt->catches) !== 1) {
            return \true;
        }
        if (\count($stmt->catches[0]->types) !== 1) {
            return \true;
        }
        $catch = $stmt->catches[0];
        return !$catch->var instanceof Variable;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function renameVariableInStmts(Catch_ $catch, string $oldVariableName, string $newVariableName, int $key, array $stmts, ?Stmt $stmt) : void
    {
        $this->traverseNodesWithCallable($catch->stmts, function (Node $node) use($oldVariableName, $newVariableName) {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $oldVariableName)) {
                return null;
            }
            $node->name = $newVariableName;
            return null;
        });
        $this->replaceNextUsageVariable($oldVariableName, $newVariableName, $key, $stmts, $stmt);
    }
    /**
     * @param Stmt[] $stmts
     */
    private function replaceNextUsageVariable(string $oldVariableName, string $newVariableName, int $key, array $stmts, ?Node $nextNode) : void
    {
        if (!$nextNode instanceof Node) {
            return;
        }
        $nonAssignedVariables = [];
        $this->traverseNodesWithCallable($nextNode, function (Node $node) use($oldVariableName, &$nonAssignedVariables) : ?int {
            if ($node instanceof Assign && $node->var instanceof Variable) {
                return NodeTraverser::STOP_TRAVERSAL;
            }
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $oldVariableName)) {
                return null;
            }
            $nonAssignedVariables[] = $node;
            return null;
        });
        foreach ($nonAssignedVariables as $nonAssignedVariable) {
            $nonAssignedVariable->name = $newVariableName;
        }
        if (!isset($stmts[$key + 1])) {
            return;
        }
        if (!isset($stmts[$key + 2])) {
            return;
        }
        $nextNode = $stmts[$key + 2];
        $key += 2;
        $this->replaceNextUsageVariable($oldVariableName, $newVariableName, $key, $stmts, $nextNode);
    }
    private function resolveVariableName(Name $name) : string
    {
        $originalName = $name->getAttribute(AttributeKey::ORIGINAL_NAME);
        // this allows to respect the name alias, if used
        if ($originalName instanceof Name) {
            return $originalName->toString();
        }
        return $name->toString();
    }
}

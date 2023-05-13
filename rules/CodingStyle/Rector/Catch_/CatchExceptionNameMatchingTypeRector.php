<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Catch_;

use RectorPrefix202305\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\TryCatch;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\AliasNameResolver;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\CatchExceptionNameMatchingTypeRectorTest
 */
final class CatchExceptionNameMatchingTypeRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/xmfMAX/1
     */
    private const STARTS_WITH_ABBREVIATION_REGEX = '#^([A-Za-z]+?)([A-Z]{1}[a-z]{1})([A-Za-z]*)#';
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\AliasNameResolver
     */
    private $aliasNameResolver;
    public function __construct(PropertyNaming $propertyNaming, AliasNameResolver $aliasNameResolver)
    {
        $this->propertyNaming = $propertyNaming;
        $this->aliasNameResolver = $aliasNameResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Type and name of catch exception should match', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $typoException) {
            $typoException->getMessage();
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // ...
        } catch (SomeException $someException) {
            $someException->getMessage();
        }
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
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
            /** @var TryCatch $stmt */
            $catch = $stmt->catches[0];
            /** @var Variable $catchVar */
            $catchVar = $catch->var;
            /** @var string $oldVariableName */
            $oldVariableName = (string) $this->getName($catchVar);
            $type = $catch->types[0];
            $typeShortName = $this->nodeNameResolver->getShortName($type);
            $aliasName = $this->aliasNameResolver->resolveByName($type);
            if (\is_string($aliasName)) {
                $typeShortName = $aliasName;
            }
            $newVariableName = $this->resolveNewVariableName($typeShortName);
            $objectType = new ObjectType($newVariableName);
            $newVariableName = $this->propertyNaming->fqnToVariableName($objectType);
            if ($oldVariableName === $newVariableName) {
                continue;
            }
            // variable defined first only resolvable by Scope pulled from Stmt
            $scope = $stmt->getAttribute(AttributeKey::SCOPE);
            if (!$scope instanceof Scope) {
                continue;
            }
            $isFoundInPrevious = $scope->hasVariableType($newVariableName)->yes();
            if ($isFoundInPrevious) {
                return null;
            }
            $catch->var = new Variable($newVariableName);
            $this->renameVariableInStmts($catch, $stmt, $oldVariableName, $newVariableName, $key, $node->stmts, $node->stmts[$key + 1] ?? null);
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
        if (!$catch->var instanceof Variable) {
            return \true;
        }
        $parentNode = $stmt->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof FileWithoutNamespace || $parentNode instanceof Namespace_) {
            return \false;
        }
        return !$parentNode instanceof FunctionLike;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function renameVariableInStmts(Catch_ $catch, TryCatch $tryCatch, string $oldVariableName, string $newVariableName, int $key, array $stmts, ?Stmt $stmt) : void
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
        $this->replaceNextUsageVariable($tryCatch, $oldVariableName, $newVariableName, $key, $stmts, $stmt);
    }
    /**
     * @param Stmt[] $stmts
     */
    private function replaceNextUsageVariable(Node $currentNode, string $oldVariableName, string $newVariableName, int $key, array $stmts, ?Node $nextNode) : void
    {
        if (!$nextNode instanceof Node) {
            return;
        }
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->find($nextNode, function (Node $node) use($oldVariableName) : bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $oldVariableName);
        });
        $processRenameVariables = $this->processRenameVariable($variables, $oldVariableName, $newVariableName);
        if (!$processRenameVariables) {
            return;
        }
        if (!isset($stmts[$key + 1])) {
            return;
        }
        $currentNode = $stmts[$key + 1];
        if (!isset($stmts[$key + 2])) {
            return;
        }
        $nextNode = $stmts[$key + 2];
        $key += 2;
        $this->replaceNextUsageVariable($currentNode, $oldVariableName, $newVariableName, $key, $stmts, $nextNode);
    }
    /**
     * @param Variable[] $variables
     */
    private function processRenameVariable(array $variables, string $oldVariableName, string $newVariableName) : bool
    {
        foreach ($variables as $variable) {
            $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Assign && $this->nodeComparator->areNodesEqual($parentNode->var, $variable) && $this->nodeNameResolver->isName($parentNode->var, $oldVariableName) && !$this->nodeComparator->areNodesEqual($parentNode->expr, $variable)) {
                return \false;
            }
            $variable->name = $newVariableName;
        }
        return \true;
    }
}

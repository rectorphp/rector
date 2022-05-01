<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Catch_;

use RectorPrefix20220501\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\TryCatch;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\AliasNameResolver;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector\CatchExceptionNameMatchingTypeRectorTest
 */
final class CatchExceptionNameMatchingTypeRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Naming\Naming\AliasNameResolver $aliasNameResolver)
    {
        $this->propertyNaming = $propertyNaming;
        $this->aliasNameResolver = $aliasNameResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Type and name of catch exception should match', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Catch_::class];
    }
    /**
     * @param Catch_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (\count($node->types) !== 1) {
            return null;
        }
        if ($node->var === null) {
            return null;
        }
        $oldVariableName = $this->getName($node->var);
        if (!\is_string($oldVariableName)) {
            return null;
        }
        $type = $node->types[0];
        $typeShortName = $this->nodeNameResolver->getShortName($type);
        $aliasName = $this->aliasNameResolver->resolveByName($type);
        if (\is_string($aliasName)) {
            $typeShortName = $aliasName;
        }
        $newVariableName = \RectorPrefix20220501\Nette\Utils\Strings::replace(\lcfirst($typeShortName), self::STARTS_WITH_ABBREVIATION_REGEX, function (array $matches) : string {
            $output = '';
            $output .= isset($matches[1]) ? \strtolower($matches[1]) : '';
            $output .= $matches[2] ?? '';
            return $output . ($matches[3] ?? '');
        });
        $objectType = new \PHPStan\Type\ObjectType($newVariableName);
        $newVariableName = $this->propertyNaming->fqnToVariableName($objectType);
        if ($oldVariableName === $newVariableName) {
            return null;
        }
        $newVariable = new \PhpParser\Node\Expr\Variable($newVariableName);
        $isFoundInPrevious = (bool) $this->betterNodeFinder->findFirstPreviousOfNode($node, function (\PhpParser\Node $n) use($newVariable) : bool {
            return $this->nodeComparator->areNodesEqual($n, $newVariable);
        });
        if ($isFoundInPrevious) {
            return null;
        }
        $node->var->name = $newVariableName;
        $this->renameVariableInStmts($node, $oldVariableName, $newVariableName);
        return $node;
    }
    private function renameVariableInStmts(\PhpParser\Node\Stmt\Catch_ $catch, string $oldVariableName, string $newVariableName) : void
    {
        $this->traverseNodesWithCallable($catch->stmts, function (\PhpParser\Node $node) use($oldVariableName, $newVariableName) {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $oldVariableName)) {
                return null;
            }
            $node->name = $newVariableName;
            return null;
        });
        /** @var TryCatch $tryCatch */
        $tryCatch = $catch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $next = $tryCatch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        $this->replaceNextUsageVariable($tryCatch, $next, $oldVariableName, $newVariableName);
    }
    private function replaceNextUsageVariable(\PhpParser\Node $currentNode, ?\PhpParser\Node $nextNode, string $oldVariableName, string $newVariableName) : void
    {
        if (!$nextNode instanceof \PhpParser\Node) {
            $parent = $currentNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if (!$parent instanceof \PhpParser\Node) {
                return;
            }
            if ($parent instanceof \PhpParser\Node\FunctionLike) {
                return;
            }
            $nextNode = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
            $this->replaceNextUsageVariable($parent, $nextNode, $oldVariableName, $newVariableName);
            return;
        }
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->find($nextNode, function (\PhpParser\Node $node) use($oldVariableName) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $oldVariableName);
        });
        $processRenameVariables = $this->processRenameVariable($variables, $oldVariableName, $newVariableName);
        if (!$processRenameVariables) {
            return;
        }
        $currentNode = $nextNode;
        $nextNode = $nextNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        $this->replaceNextUsageVariable($currentNode, $nextNode, $oldVariableName, $newVariableName);
    }
    /**
     * @param Variable[] $variables
     */
    private function processRenameVariable(array $variables, string $oldVariableName, string $newVariableName) : bool
    {
        foreach ($variables as $variable) {
            $parent = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parent instanceof \PhpParser\Node\Expr\Assign && $this->nodeComparator->areNodesEqual($parent->var, $variable) && $this->nodeNameResolver->isName($parent->var, $oldVariableName) && !$this->nodeComparator->areNodesEqual($parent->expr, $variable)) {
                return \false;
            }
            $variable->name = $newVariableName;
        }
        return \true;
    }
}

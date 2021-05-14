<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Catch_;

use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Catch_;
use Rector\Core\Rector\AbstractRector;
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
        if (!$oldVariableName) {
            return null;
        }
        $type = $node->types[0];
        $typeShortName = $this->nodeNameResolver->getShortName($type);
        $newVariableName = \RectorPrefix20210514\Nette\Utils\Strings::replace(\lcfirst($typeShortName), self::STARTS_WITH_ABBREVIATION_REGEX, function (array $matches) : string {
            $output = '';
            $output .= isset($matches[1]) ? \strtolower($matches[1]) : '';
            $output .= $matches[2] ?? '';
            $output .= $matches[3] ?? '';
            return $output;
        });
        if ($oldVariableName === $newVariableName) {
            return null;
        }
        $newVariable = new \PhpParser\Node\Expr\Variable($newVariableName);
        $isFoundInPrevious = (bool) $this->betterNodeFinder->findFirstPrevious($node, function (\PhpParser\Node $n) use($newVariable) : bool {
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
        $this->traverseNodesWithCallable($catch->stmts, function (\PhpParser\Node $node) use($oldVariableName, $newVariableName) : void {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return;
            }
            if (!$this->nodeNameResolver->isName($node, $oldVariableName)) {
                return;
            }
            $node->name = $newVariableName;
        });
    }
}

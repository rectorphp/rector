<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Catch_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector\CatchExceptionNameMatchingTypeRectorTest
 */
final class CatchExceptionNameMatchingTypeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Type and name of catch exception should match', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Catch_::class];
    }

    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->types) !== 1) {
            return null;
        }

        $type = $node->types[0];
        $typeShortName = $this->getShortName($type);

        if ($node->var === null) {
            return null;
        }

        $oldVariableName = $this->getName($node->var);
        if (! $oldVariableName) {
            return null;
        }

        $newVariableName = lcfirst($typeShortName);
        if ($oldVariableName === $newVariableName) {
            return null;
        }

        $node->var->name = $newVariableName;

        $this->renameVariableInStmts($node, $oldVariableName, $newVariableName);

        return $node;
    }

    private function renameVariableInStmts(Catch_ $catch, string $oldVariableName, string $newVariableName): void
    {
        $this->traverseNodesWithCallable($catch->stmts, function (Node $node) use (
            $oldVariableName,
            $newVariableName
        ): void {
            if (! $this->isVariableName($node, $oldVariableName)) {
                return;
            }

            $node->name = $newVariableName;
        });
    }
}

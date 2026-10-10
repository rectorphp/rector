<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\StmtsAwareInterface;

use PhpParser\Node;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\FileNode;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as too risky to add `declare(strict_types=1)` without context about type safety. Use SafeDeclareStrictTypesRector instead, which only adds it to type-safe files.
 */
final class DeclareStrictTypesRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add `declare(strict_types=1)` if missing in a namespaced file', [new CodeSample(<<<'CODE_SAMPLE'
namespace App;

class SomeClass
{
    function someFunction(int $number)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
declare(strict_types=1);

namespace App;

class SomeClass
{
    function someFunction(int $number)
    {
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
        return [FileNode::class];
    }
    /**
     * @param FileNode $node
     */
    public function refactor(Node $node): ?FileNode
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as too risky without type-safety context. Use SafeDeclareStrictTypesRector instead', self::class));
    }
}

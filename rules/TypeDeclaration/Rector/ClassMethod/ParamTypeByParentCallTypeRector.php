<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as it can add an invalid param type inferred from a single parent call usage.
 */
final class ParamTypeByParentCallTypeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change param type based on parent param type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeControl
{
    public function __construct(string $name)
    {
    }
}

class VideoControl extends SomeControl
{
    public function __construct($name)
    {
        parent::__construct($name);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeControl
{
    public function __construct(string $name)
    {
    }
}

class VideoControl extends SomeControl
{
    public function __construct(string $name)
    {
        parent::__construct($name);
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated', self::class));
    }
}

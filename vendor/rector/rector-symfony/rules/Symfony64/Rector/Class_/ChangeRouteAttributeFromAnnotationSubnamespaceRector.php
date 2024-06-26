<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony64\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated since Rector 1.1.2. Use @see RenameAttributeRector rule instead.
 */
final class ChangeRouteAttributeFromAnnotationSubnamespaceRector extends AbstractRector implements DeprecatedInterface
{
    /**
     * @var bool
     */
    private $hasWarned = \false;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace Symfony\\Component\\Routing\\Annotation\\Route by Symfony\\Component\\Routing\\Attribute\\Route when the class use #Route[] attribute', [new CodeSample(<<<'CODE_SAMPLE'
#[\Symfony\Component\Routing\Annotation\Route("/foo")]
public function create(Request $request): Response
{
    return new Response();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[\Symfony\Component\Routing\Attribute\Route('/foo')]
public function create(Request $request): Response
{
    return new Response();
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Class_::class];
    }
    /**
     * @param ClassMethod|Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->hasWarned) {
            return null;
        }
        \trigger_error(\sprintf('The "%s" rule was deprecated. Use RenameAttributeRector rule instead', self::class));
        \sleep(3);
        $this->hasWarned = \true;
        return null;
    }
}

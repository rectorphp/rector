<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Trait_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Matches a very narrow trait shape - exactly one property and one required setter/getter pair - to infer a return type. The match is too fragile and risky for the tiny gain, and PHPStan already reports the missing return type.
 */
final class AddTraitGetterReturnTypeBasedOnSetterRequiredRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add trait getter return type based on setter with @required annotation or #[\Symfony\Contracts\Service\Attribute\Required] attribute', [new CodeSample(<<<'CODE_SAMPLE'
use stdClass;

trait SomeTrait
{
    private $service;

    public function getService()
    {
        return $this->service;
    }

    /**
     * @required
     */
    public function setService(stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use stdClass;

trait SomeTrait
{
    private $service;

    public function getService(): stdClass
    {
        return $this->service;
    }

    /**
     * @required
     */
    public function setService(stdClass $stdClass)
    {
        $this->stdClass = $stdClass;
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
        return [Trait_::class];
    }
    /**
     * @param Trait_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as it matches a very narrow and fragile trait shape for a tiny gain.', self::class));
    }
}

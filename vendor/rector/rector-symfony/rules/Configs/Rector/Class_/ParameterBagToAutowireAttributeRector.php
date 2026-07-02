<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Too specific and opinionated to maintain as a generic rule. Write a custom rule for your own ParameterBag-to-#[Autowire] convention instead.
 */
final class ParameterBagToAutowireAttributeRector extends AbstractRector implements DeprecatedInterface
{
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as too specific and opinionated to maintain as a generic rule. Write a custom rule for your own ParameterBag-to-#[Autowire] convention instead', self::class));
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change explicit configuration parameter pass into #[Autowire] attributes', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class CertificateFactory
{
    private ?string $certName;

    public function __construct(
        ParameterBagInterface $parameterBag
    ) {
        $this->certName = $parameterBag->get('certificate_name');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CertificateFactory
{
    private ?string $certName;

    public function __construct(
        #[Autowire(param: 'certificate_name')]
        $certName,
    ) {
        $this->certName = $certName;
    }
}
CODE_SAMPLE
)]);
    }
}

<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony44\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony44\Rector\MethodCall\WebLinkStringRelationsToConstantsRector\WebLinkStringRelationsToConstantsRectorTest
 */
final class WebLinkStringRelationsToConstantsRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var mixed[]
     */
    private const EXACT_MAP = ['openid2.local_id' => 'REL_OPENID_2_LOCAL_ID', 'openid2.provider' => 'REL_OPENID_2_PROVIDER', 'p3pv1' => 'REL_P_3_PV_1'];
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/web-link', '>= 4.4');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change string link relations in WebLink to use Link constants', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\WebLink\Link;

$link = (new Link('preload', 'https://...'))->withRel('next');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\WebLink\Link;

$link = (new Link(Link::REL_PRELOAD, 'https://...'))->withRel(Link::REL_NEXT);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [MethodCall::class, New_::class];
    }
    /**
     * @param MethodCall|New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof MethodCall) {
            if (!$this->isObjectType($node->var, new ObjectType('Symfony\Component\WebLink\Link'))) {
                return null;
            }
            if (!$this->isNames($node->name, ['withRel', 'withoutRel'])) {
                return null;
            }
        } elseif ($node instanceof New_) {
            if (!$this->isObjectType($node->class, new ObjectType('Symfony\Component\WebLink\Link'))) {
                return null;
            }
        }
        $args = $node->getArgs();
        if (!isset($args[0])) {
            return null;
        }
        $valueNode = $args[0]->value;
        if (!$valueNode instanceof String_) {
            return null;
        }
        $constFetch = $this->createConstantFetch($valueNode->value);
        if (!$constFetch) {
            return null;
        }
        $args[0]->value = $constFetch;
        return $node;
    }
    private function createConstantFetch(string $relation): ?Node
    {
        $constName = self::EXACT_MAP[$relation] ?? 'REL_' . strtoupper(str_replace('-', '_', $relation));
        if (!$this->reflectionProvider->hasClass('Symfony\Component\WebLink\Link')) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass('Symfony\Component\WebLink\Link');
        if (!$classReflection->hasConstant($constName)) {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch('Symfony\Component\WebLink\Link', $constName);
    }
}

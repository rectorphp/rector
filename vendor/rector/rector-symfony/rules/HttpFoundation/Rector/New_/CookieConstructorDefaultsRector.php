<?php

declare (strict_types=1);
namespace Rector\Symfony\HttpFoundation\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see \Rector\Symfony\Tests\HttpFoundation\Rector\New_\CookieConstructorDefaultsRector\CookieConstructorDefaultsRectorTest
 */
final class CookieConstructorDefaultsRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/http-foundation', '>=4.2');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Update the Cookie constructor default values changed in Symfony 4.2: $secure "false" to "null" and $sameSite "null" to "lax"', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Cookie;

$cookie = new Cookie('name', 'value', 0, '/', null, false, true, false, null);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Cookie;

$cookie = new Cookie('name', 'value', 0, '/', null, null, true, false, 'lax');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?New_
    {
        if (!$this->isObjectType($node->class, new ObjectType(SymfonyClass::COOKIE))) {
            return null;
        }
        $args = $node->getArgs();
        $hasChanged = \false;
        $secureArg = $args[5] ?? null;
        if ($secureArg instanceof Arg && !$secureArg->name instanceof Identifier && $this->valueResolver->isFalse($secureArg->value)) {
            $secureArg->value = $this->nodeFactory->createNull();
            $hasChanged = \true;
        }
        $sameSiteArg = $args[8] ?? null;
        if ($sameSiteArg instanceof Arg && !$sameSiteArg->name instanceof Identifier && $this->valueResolver->isNull($sameSiteArg->value)) {
            $sameSiteArg->value = new String_('lax');
            $hasChanged = \true;
        }
        return $hasChanged ? $node : null;
    }
}

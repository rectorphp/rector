<?php

declare (strict_types=1);
namespace Rector\Symfony\HttpFoundation\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see \Rector\Symfony\Tests\HttpFoundation\Rector\New_\CookieSameSiteConstantRector\CookieSameSiteConstantRectorTest
 */
final class CookieSameSiteConstantRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @var array<string, string>
     */
    private const SAME_SITE_TO_CONSTANT = ['lax' => 'SAMESITE_LAX', 'strict' => 'SAMESITE_STRICT', 'none' => 'SAMESITE_NONE'];
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/http-foundation', '>=4.2');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace the "same site" magic string of Cookie with a Cookie constant', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Cookie;

$cookie = Cookie::create('name', 'value', 0, '/', null, false, true, false, 'lax');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Cookie;

$cookie = Cookie::create('name', 'value', 0, '/', null, false, true, false, Cookie::SAMESITE_LAX);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class, StaticCall::class, MethodCall::class];
    }
    /**
     * @param New_|StaticCall|MethodCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\StaticCall|null
     */
    public function refactor(Node $node)
    {
        $position = $this->matchSameSiteArgPosition($node);
        if ($position === null) {
            return null;
        }
        $sameSiteArg = $node->getArgs()[$position] ?? null;
        if (!$sameSiteArg instanceof Arg || $sameSiteArg->name instanceof Identifier) {
            return null;
        }
        // only a plain string literal is replaced, the constant itself has the same value
        if (!$sameSiteArg->value instanceof String_) {
            return null;
        }
        $constantName = self::SAME_SITE_TO_CONSTANT[$sameSiteArg->value->value] ?? null;
        if ($constantName === null) {
            return null;
        }
        $sameSiteArg->value = $this->nodeFactory->createClassConstFetch(SymfonyClass::COOKIE, $constantName);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\StaticCall $node
     */
    private function matchSameSiteArgPosition($node): ?int
    {
        if ($node instanceof New_) {
            return $this->isObjectType($node->class, new ObjectType(SymfonyClass::COOKIE)) ? 8 : null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($node instanceof StaticCall) {
            if (!$this->isName($node->class, SymfonyClass::COOKIE)) {
                return null;
            }
            return $this->isName($node->name, 'create') ? 8 : null;
        }
        if (!$this->isObjectType($node->var, new ObjectType(SymfonyClass::COOKIE))) {
            return null;
        }
        return $this->isName($node->name, 'withSameSite') ? 0 : null;
    }
}

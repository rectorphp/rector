<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony81\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/8.2/UPGRADE-8.1.md#security
 *
 * @see \Rector\Symfony\Tests\Symfony81\Rector\New_\RemoveEraseCredentialsFromAuthenticatorManagerRector\RemoveEraseCredentialsFromAuthenticatorManagerRectorTest
 */
final class RemoveEraseCredentialsFromAuthenticatorManagerRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused $eraseCredentials argument from AuthenticatorManager constructor', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;

final class Foo
{
    public function bar(): void
    {
        new AuthenticatorManager(
            $authenticators,
            $tokenStorage,
            $eventDispatcher,
            $firewallName,
            true
        );                                
    }
}    
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;

final class Foo
{
    public function bar(): void
    {
        new AuthenticatorManager(
            $authenticators,
            $tokenStorage,
            $eventDispatcher,
            $firewallName,
        );                                
    }
}    
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->class, 'Symfony\Component\Security\Http\Authentication\AuthenticatorManager')) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->args as $key => $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if ($arg->name instanceof Identifier && ($this->isName($arg->name, 'exposeSecurityErrors') || $this->isName($arg->name, 'eraseCredentials')) && $this->valueResolver->isTrueOrFalse($arg->value)) {
                unset($node->args[$key]);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged && isset($node->args[5]) && $node->args[5] instanceof Arg && $this->valueResolver->isTrueOrFalse($node->args[5]->value)) {
            unset($node->args[5]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        $node->args = array_values($node->args);
        return $node;
    }
}

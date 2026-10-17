<?php

declare (strict_types=1);
namespace Rector\Symfony\Routing\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
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
 * @see \Rector\Symfony\Tests\Routing\Rector\MethodCall\UrlGeneratorReferenceTypeRector\UrlGeneratorReferenceTypeRectorTest
 */
final class UrlGeneratorReferenceTypeRector extends AbstractRector implements ComposerPackageConstraintInterface
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
        return new ComposerPackageConstraint('symfony/routing', '>=2.8');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace the magic reference value of UrlGeneratorInterface::generate() with a UrlGeneratorInterface constant', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SomeController
{
    public function run(UrlGeneratorInterface $urlGenerator)
    {
        return $urlGenerator->generate('homepage', [], true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SomeController
{
    public function run(UrlGeneratorInterface $urlGenerator)
    {
        return $urlGenerator->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?MethodCall
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'generate')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType(SymfonyClass::URL_GENERATOR_INTERFACE))) {
            return null;
        }
        $args = $node->getArgs();
        $referenceArg = $args[2] ?? null;
        if (!$referenceArg instanceof Arg || $referenceArg->name instanceof Identifier) {
            return null;
        }
        $constantName = $this->matchReferenceTypeConstant($referenceArg);
        if ($constantName === null) {
            return null;
        }
        $referenceArg->value = $this->nodeFactory->createClassConstFetch(SymfonyClass::URL_GENERATOR_INTERFACE, $constantName);
        return $node;
    }
    private function matchReferenceTypeConstant(Arg $arg): ?string
    {
        if ($this->valueResolver->isTrue($arg->value)) {
            return 'ABSOLUTE_URL';
        }
        if ($this->valueResolver->isFalse($arg->value)) {
            return 'ABSOLUTE_PATH';
        }
        if (!$arg->value instanceof String_) {
            return null;
        }
        switch ($arg->value->value) {
            case 'relative':
                return 'RELATIVE_PATH';
            case 'network':
                return 'NETWORK_PATH';
            default:
                return null;
        }
    }
}

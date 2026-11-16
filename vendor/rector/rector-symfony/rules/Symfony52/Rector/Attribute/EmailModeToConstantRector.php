<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony52\Rector\Attribute;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see \Rector\Symfony\Tests\Symfony52\Rector\Attribute\EmailModeToConstantRector\EmailModeToConstantRectorTest
 */
final class EmailModeToConstantRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string
     */
    private const EMAIL_CLASS = 'Symfony\Component\Validator\Constraints\Email';
    /**
     * @var string
     */
    private const VALIDATION_MODE_PREFIX = 'VALIDATION_MODE_';
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/validator', '>=5.2');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace string mode in #[Email] attribute with Email class constant', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints\Email;

final class SomeClass
{
    #[Email(mode: 'strict')]
    public $email;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints\Email;

final class SomeClass
{
    #[Email(mode: Email::VALIDATION_MODE_STRICT)]
    public $email;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Attribute::class];
    }
    /**
     * @param Attribute $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, self::EMAIL_CLASS)) {
            return null;
        }
        $modeArg = $this->findModeArg($node);
        if (!$modeArg instanceof Arg || !$modeArg->value instanceof String_) {
            return null;
        }
        $constantName = $this->resolveConstantNamesByValue()[$modeArg->value->value] ?? null;
        if ($constantName === null) {
            return null;
        }
        $modeArg->value = $this->nodeFactory->createClassConstFetch(self::EMAIL_CLASS, $constantName);
        return $node;
    }
    private function findModeArg(Attribute $attribute): ?Arg
    {
        foreach ($attribute->args as $arg) {
            if ($arg->name instanceof Identifier && $this->isName($arg->name, 'mode')) {
                return $arg;
            }
        }
        return null;
    }
    /**
     * @return array<string, string>
     */
    private function resolveConstantNamesByValue(): array
    {
        if (!$this->reflectionProvider->hasClass(self::EMAIL_CLASS)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass(self::EMAIL_CLASS);
        $constantNamesByValue = [];
        foreach ($classReflection->getNativeReflection()->getConstants() as $constantName => $constantValue) {
            if (strncmp($constantName, self::VALIDATION_MODE_PREFIX, strlen(self::VALIDATION_MODE_PREFIX)) !== 0) {
                continue;
            }
            if (!is_string($constantValue)) {
                continue;
            }
            $constantNamesByValue[$constantValue] = $constantName;
        }
        return $constantNamesByValue;
    }
}

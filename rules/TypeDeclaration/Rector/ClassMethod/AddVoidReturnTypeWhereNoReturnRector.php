<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\NeverType;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\AddVoidReturnTypeWhereNoReturnRectorTest
 */
final class AddVoidReturnTypeWhereNoReturnRector extends AbstractRector implements MinPhpVersionInterface, AllowEmptyConfigurableRectorInterface
{
    /**
     * @var string using phpdoc instead of a native void type can ease the migration path for consumers of code being processed.
     */
    public const USE_PHPDOC = 'use_phpdoc';

    private bool $usePhpdoc = false;

    public function __construct(
        private readonly SilentVoidResolver $silentVoidResolver,
        private readonly ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver,
        private readonly PhpDocTypeChanger $phpDocTypeChanger,
        private readonly ReflectionResolver $reflectionResolver
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add return type void to function like without any return', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValues()
    {
        $value = 1000;
        return;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValues(): void
    {
        $value = 1000;
        return;
    }
}
CODE_SAMPLE
                ,
                [
                    self::USE_PHPDOC => false,
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->returnType !== null) {
            return null;
        }

        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        if (! $this->silentVoidResolver->hasExclusiveVoid($node)) {
            return null;
        }

        if ($this->usePhpdoc) {
            $this->changePhpDocToVoidIfNotNever($node);

            return $node;
        }

        if ($node instanceof ClassMethod && $this->classMethodReturnVendorLockResolver->isVendorLocked($node)) {
            return null;
        }

        $node->returnType = new Identifier('void');
        return $node;
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::VOID_TYPE;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $usePhpdoc = $configuration[self::USE_PHPDOC] ?? (bool) current($configuration);
        Assert::boolean($usePhpdoc);

        $this->usePhpdoc = $usePhpdoc;
    }

    private function changePhpDocToVoidIfNotNever(ClassMethod|Function_|Closure|Node $node): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        if ($phpDocInfo->getReturnType() instanceof NeverType) {
            return;
        }

        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, new VoidType());
    }

    private function shouldSkipClassMethod(ClassMethod|Function_|Closure $functionLike): bool
    {
        if (! $functionLike instanceof ClassMethod) {
            return false;
        }

        if ($functionLike->isMagic()) {
            return true;
        }

        if ($functionLike->isAbstract()) {
            return true;
        }

        if ($functionLike->isProtected()) {
            return ! $this->isInsideFinalClass($functionLike);
        }

        return false;
    }

    private function isInsideFinalClass(ClassMethod $classMethod): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        return $classReflection->isFinal();
    }
}

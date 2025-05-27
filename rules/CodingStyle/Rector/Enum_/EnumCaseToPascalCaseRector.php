<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Enum_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\BetterReflection\Reflection\ReflectionEnum;
use PHPStan\BetterReflection\Reflector\DefaultReflector;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Rector\AbstractRector;
use Rector\Skipper\FileSystem\PathNormalizer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Enum_\EnumCaseToPascalCaseRector\EnumCaseToPascalCaseRectorTest
 */
final class EnumCaseToPascalCaseRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private DynamicSourceLocatorProvider $dynamicSourceLocatorProvider;
    public function __construct(ReflectionProvider $reflectionProvider, DynamicSourceLocatorProvider $dynamicSourceLocatorProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->dynamicSourceLocatorProvider = $dynamicSourceLocatorProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert enum cases to PascalCase and update their usages', [new CodeSample(<<<'CODE_SAMPLE'
enum Status
{
    case PENDING;
    case published;
    case IN_REVIEW;
    case waiting_for_approval;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
enum Status
{
    case Pending;
    case Published;
    case InReview;
    case WaitingForApproval;
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Enum_::class, ClassConstFetch::class];
    }
    /**
     * @param Enum_|ClassConstFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Enum_) {
            return $this->refactorEnum($node);
        }
        if ($node instanceof ClassConstFetch) {
            return $this->refactorClassConstFetch($node);
        }
        return null;
    }
    public function refactorEnum(Enum_ $enum) : ?\PhpParser\Node\Stmt\Enum_
    {
        $enumName = $this->getName($enum);
        if ($enumName === null) {
            return null;
        }
        $hasChanged = \false;
        foreach ($enum->stmts as $stmt) {
            if (!$stmt instanceof EnumCase) {
                continue;
            }
            $currentName = $stmt->name->toString();
            $pascalCaseName = $this->convertToPascalCase($currentName);
            if ($currentName === $pascalCaseName) {
                continue;
            }
            $stmt->name = new Identifier($pascalCaseName);
            $hasChanged = \true;
        }
        return $hasChanged ? $enum : null;
    }
    private function refactorClassConstFetch(ClassConstFetch $classConstFetch) : ?Node
    {
        if (!$classConstFetch->class instanceof Name) {
            return null;
        }
        if (!$classConstFetch->name instanceof Identifier) {
            return null;
        }
        if ($this->nodeTypeResolver->getType($classConstFetch->class)->isEnum()->no()) {
            return null;
        }
        $constName = $classConstFetch->name->toString();
        // Skip "class" constant
        if ($constName === 'class') {
            return null;
        }
        $enumClassName = $classConstFetch->class->toString();
        if (!$this->reflectionProvider->hasClass($enumClassName)) {
            return null;
        }
        $sourceLocator = $this->dynamicSourceLocatorProvider->provide();
        $defaultReflector = new DefaultReflector($sourceLocator);
        try {
            $classIdentifier = $defaultReflector->reflectClass($classConstFetch->class->toString());
        } catch (IdentifierNotFound $exception) {
            // source is outside the paths defined in withPaths(), eg: vendor
            return null;
        }
        // ensure exactly ReflectionEnum
        if (!$classIdentifier instanceof ReflectionEnum) {
            return null;
        }
        // ensure not part of definition in ->withAutoloadPaths()
        $fileTarget = $classIdentifier->getFileName();
        // possibly native
        if ($fileTarget === null) {
            return null;
        }
        $autoloadPaths = SimpleParameterProvider::provideArrayParameter(Option::AUTOLOAD_PATHS);
        $normalizedFileTarget = PathNormalizer::normalize((string) \realpath($fileTarget));
        foreach ($autoloadPaths as $autoloadPath) {
            $normalizedAutoloadPath = PathNormalizer::normalize($autoloadPath);
            if ($autoloadPath === $fileTarget) {
                return null;
            }
            if (\strncmp($normalizedFileTarget, $normalizedAutoloadPath . '/', \strlen($normalizedAutoloadPath . '/')) === 0) {
                return null;
            }
        }
        $pascalCaseName = $this->convertToPascalCase($constName);
        if ($constName !== $pascalCaseName) {
            $classConstFetch->name = new Identifier($pascalCaseName);
            return $classConstFetch;
        }
        return null;
    }
    private function convertToPascalCase(string $name) : string
    {
        $parts = \explode('_', \strtolower($name));
        return \implode('', \array_map(\Closure::fromCallable('ucfirst'), $parts));
    }
}

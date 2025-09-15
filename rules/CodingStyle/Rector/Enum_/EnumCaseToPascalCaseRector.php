<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Enum_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\BetterReflection\Reflector\DefaultReflector;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\Reflection\ClassReflection;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Rector\AbstractRector;
use Rector\Skipper\FileSystem\PathNormalizer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Enum_\EnumCaseToPascalCaseRector\EnumCaseToPascalCaseRectorTest
 * @see \Rector\Tests\CodingStyle\Rector\Enum_\EnumCaseToPascalCaseRector\WithAutoloadPathsTest
 */
final class EnumCaseToPascalCaseRector extends AbstractRector
{
    /**
     * @readonly
     */
    private DynamicSourceLocatorProvider $dynamicSourceLocatorProvider;
    public function __construct(DynamicSourceLocatorProvider $dynamicSourceLocatorProvider)
    {
        $this->dynamicSourceLocatorProvider = $dynamicSourceLocatorProvider;
    }
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [Enum_::class, ClassConstFetch::class];
    }
    /**
     * @param Enum_|ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Enum_) {
            return $this->refactorEnum($node);
        }
        if ($node instanceof ClassConstFetch) {
            return $this->refactorClassConstFetch($node);
        }
        return null;
    }
    public function refactorEnum(Enum_ $enum): ?\PhpParser\Node\Stmt\Enum_
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
    private function refactorClassConstFetch(ClassConstFetch $classConstFetch): ?Node
    {
        if (!$classConstFetch->class instanceof Name) {
            return null;
        }
        if (!$classConstFetch->name instanceof Identifier) {
            return null;
        }
        $constName = $classConstFetch->name->toString();
        $pascalCaseName = $this->convertToPascalCase($constName);
        // short circuit if already in pascal case
        if ($constName === $pascalCaseName) {
            return null;
        }
        $classReflection = $this->nodeTypeResolver->getType($classConstFetch->class)->getObjectClassReflections()[0] ?? null;
        if ($classReflection === null || !$classReflection->isEnum()) {
            return null;
        }
        if (!$this->isEnumCase($classReflection, $constName, $pascalCaseName)) {
            return null;
        }
        if ($this->isUsedOutsideOfProject($classConstFetch->class)) {
            return null;
        }
        $classConstFetch->name = new Identifier($pascalCaseName);
        return $classConstFetch;
    }
    private function isUsedOutsideOfProject(Name $name): bool
    {
        if (in_array($name->toString(), ['self', 'static'], \true)) {
            return \false;
        }
        $sourceLocator = $this->dynamicSourceLocatorProvider->provide();
        $defaultReflector = new DefaultReflector($sourceLocator);
        try {
            $classIdentifier = $defaultReflector->reflectClass($name->toString());
        } catch (IdentifierNotFound $exception) {
            // source is outside the paths defined in withPaths(), eg: vendor
            return \true;
        }
        $fileTarget = $classIdentifier->getFileName();
        // possibly native
        if ($fileTarget === null) {
            return \true;
        }
        $autoloadPaths = SimpleParameterProvider::provideArrayParameter(Option::AUTOLOAD_PATHS);
        $normalizedFileTarget = PathNormalizer::normalize((string) realpath($fileTarget));
        foreach ($autoloadPaths as $autoloadPath) {
            $normalizedAutoloadPath = PathNormalizer::normalize($autoloadPath);
            if ($autoloadPath === $fileTarget) {
                return \true;
            }
            if (strncmp($normalizedFileTarget, $normalizedAutoloadPath . '/', strlen($normalizedAutoloadPath . '/')) === 0) {
                return \true;
            }
        }
        return \false;
    }
    private function isEnumCase(ClassReflection $classReflection, string $name, string $pascalName): bool
    {
        // the enum case might have already been renamed, need to check both
        if ($classReflection->hasEnumCase($name)) {
            return \true;
        }
        return $classReflection->hasEnumCase($pascalName);
    }
    private function convertToPascalCase(string $name): string
    {
        $parts = explode('_', $name);
        return implode('', array_map(fn($part): string => ctype_upper($part) ? ucfirst(strtolower($part)) : ucfirst($part), $parts));
    }
}

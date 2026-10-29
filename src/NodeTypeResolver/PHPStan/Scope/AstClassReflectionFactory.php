<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\MutatingScope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass as ReflectionClassAdapter;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionEnum as ReflectionEnumAdapter;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionEnum;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Strategy\NodeToReflection;
use PHPStan\BetterReflection\SourceLocator\Located\LocatedSource;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ClassReflectionFactory;
/**
 * The class reflection from the reflection provider is built from the original file,
 * so it does not know about members a rule added or removed; build it from the current class node instead
 *
 * @see \PHPStan\Analyser\StmtHandler\ClassLikeHandler::createAstClassReflection()
 */
final class AstClassReflectionFactory
{
    /**
     * @readonly
     */
    private Reflector $reflector;
    /**
     * @readonly
     */
    private ClassReflectionFactory $classReflectionFactory;
    /**
     * @readonly
     */
    private Standard $standardPrinter;
    /**
     * Last rebuilt reflection per class, keyed by file and class node, with the hash of the class content it was built from
     *
     * @var array<string, array{string, ClassReflection}>
     */
    private array $classReflectionsByClass = [];
    public function __construct(Reflector $reflector, ClassReflectionFactory $classReflectionFactory, Standard $standardPrinter)
    {
        $this->reflector = $reflector;
        $this->classReflectionFactory = $classReflectionFactory;
        $this->standardPrinter = $standardPrinter;
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Enum_ $classLike
     */
    public function create($classLike, MutatingScope $mutatingScope): ?ClassReflection
    {
        $filePath = $mutatingScope->getFile();
        $namespace = $mutatingScope->getNamespace();
        // the class node is refreshed on every change in it, while its members often stay the same between passes;
        // reuse the reflection built from the same content, so its cache key and member caches are not created again
        $classKey = $filePath . ':' . $namespace . '\\' . $classLike->name;
        // Hashing needs deterministic content, without Rector's output formatting and AST adjustments.
        $contentHash = hash('md5', $this->standardPrinter->prettyPrint([$classLike]));
        if (isset($this->classReflectionsByClass[$classKey]) && $this->classReflectionsByClass[$classKey][0] === $contentHash) {
            return $this->classReflectionsByClass[$classKey][1];
        }
        $betterReflectionClass = (new NodeToReflection())->__invoke($this->reflector, $classLike, new LocatedSource(null, null, $filePath), $namespace !== null ? new Namespace_(new Name($namespace)) : null);
        if (!$betterReflectionClass instanceof ReflectionClass) {
            return null;
        }
        $reflectionClassAdapter = $betterReflectionClass instanceof ReflectionEnum ? new ReflectionEnumAdapter($betterReflectionClass) : new ReflectionClassAdapter($betterReflectionClass);
        // the member caches are keyed by the class reflection cache key,
        // so each changed class content needs its own key not to read members of the former one
        $classReflection = $this->classReflectionFactory->create($betterReflectionClass->getName(), $reflectionClassAdapter, null, null, null, $filePath . ':' . $contentHash);
        $this->classReflectionsByClass[$classKey] = [$contentHash, $classReflection];
        return $classReflection;
    }
}

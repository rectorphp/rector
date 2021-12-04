<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\Application\File;
use Symplify\Astral\PhpParser\SmartPhpParser;

final class ClassLikeAstResolver
{
    /**
     * Parsing files is very heavy performance, so this will help to leverage it
     * The value can be also null, as the method might not exist in the class.
     *
     * @var array<class-string, Class_|Trait_|Interface_|null>
     */
    private array $classLikesByName = [];

    public function __construct(
        private readonly SmartPhpParser $smartPhpParser,
        private readonly BetterNodeFinder $betterNodeFinder,
    ) {
    }

    public function resolveClassFromClassReflection(
        ClassReflection $classReflection,
        string $className
    ): Trait_ | Class_ | Interface_ | Enum_ | null {
        if ($classReflection->isBuiltin()) {
            return null;
        }

        if (isset($this->classLikesByName[$classReflection->getName()])) {
            return $this->classLikesByName[$classReflection->getName()];
        }

        $fileName = $classReflection->getFileName();

        // probably internal class
        if ($fileName === null) {
            // avoid parsing falsy-file again
            $this->classLikesByName[$classReflection->getName()] = null;
            return null;
        }

        $stmts = $this->smartPhpParser->parseFile($fileName);
        if ($stmts === []) {
            // avoid parsing falsy-file again
            $this->classLikesByName[$classReflection->getName()] = null;
            return null;
        }

        /** @var array<Class_|Trait_|Interface_> $classLikes */
        $classLikes = $this->betterNodeFinder->findInstanceOf($stmts, ClassLike::class);

        $reflectionClassName = $classReflection->getName();
        foreach ($classLikes as $classLike) {
            if ($reflectionClassName !== $className) {
                continue;
            }

            $this->classLikesByName[$classReflection->getName()] = $classLike;
            return $classLike;
        }

        $this->classLikesByName[$classReflection->getName()] = null;
        return null;
    }
}

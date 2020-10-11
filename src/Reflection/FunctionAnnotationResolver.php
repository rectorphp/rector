<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpDoc\PhpDocTagsFinder;
use Rector\Core\PhpParser\Parser\FunctionParser;
use ReflectionFunction;

final class FunctionAnnotationResolver
{
    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var FunctionParser
     */
    private $functionParser;

    /**
     * @var PhpDocTagsFinder
     */
    private $phpDocTagsFinder;

    public function __construct(
        ClassNaming $classNaming,
        FunctionParser $functionParser,
        PhpDocTagsFinder $phpDocTagsFinder
    ) {
        $this->functionParser = $functionParser;
        $this->classNaming = $classNaming;
        $this->phpDocTagsFinder = $phpDocTagsFinder;
    }

    /**
     * @return mixed[]
     */
    public function extractFunctionAnnotatedThrows(ReflectionFunction $reflectionFunction): array
    {
        $docComment = $reflectionFunction->getDocComment();

        if (! is_string($docComment)) {
            return [];
        }

        $annotatedThrownClasses = $this->phpDocTagsFinder->extractTagsFromStringedDocblock($docComment, '@throws');

        return $this->expandAnnotatedClasses($reflectionFunction, $annotatedThrownClasses);
    }

    /**
     * @param mixed[] $classNames
     * @return mixed[]
     */
    private function expandAnnotatedClasses(ReflectionFunction $reflectionFunction, array $classNames): array
    {
        $namespace = $this->functionParser->parseFunction($reflectionFunction);
        if (! $namespace instanceof Namespace_) {
            return [];
        }

        $uses = $this->getUses($namespace);

        $expandedClasses = [];
        foreach ($classNames as $className) {
            $shortClassName = $this->classNaming->getShortName($className);
            $expandedClasses[] = $uses[$shortClassName] ?? $className;
        }

        return $expandedClasses;
    }

    /**
     * @return string[]
     */
    private function getUses(Namespace_ $namespace): array
    {
        $uses = [];
        foreach ($namespace->stmts as $stmt) {
            if (! $stmt instanceof Use_) {
                continue;
            }

            $use = $stmt->uses[0];
            if (! $use instanceof UseUse) {
                continue;
            }

            $parts = $use->name->parts;
            $uses[$parts[count($parts) - 1]] = implode('\\', $parts);
        }

        return $uses;
    }
}

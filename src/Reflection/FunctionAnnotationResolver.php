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

    public function extractFunctionAnnotatedThrows(ReflectionFunction $reflectionFunction): array
    {
        $functionDocblock = $reflectionFunction->getDocComment();

        if (! is_string($functionDocblock)) {
            return [];
        }

        $annotatedThrownClasses = $this->phpDocTagsFinder->extractTagsFromStringedDocblock(
            $functionDocblock,
            '@throws'
        );

        return $this->expandAnnotatedClasses($reflectionFunction, $annotatedThrownClasses);
    }

    private function expandAnnotatedClasses(ReflectionFunction $reflectionFunction, array $classNames): array
    {
        $functionNode = $this->functionParser->parseFunction($reflectionFunction);
        if (! $functionNode instanceof Namespace_) {
            return [];
        }

        $uses = $this->getUses($functionNode);

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
    private function getUses(Namespace_ $node): array
    {
        $uses = [];
        foreach ($node->stmts as $stmt) {
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

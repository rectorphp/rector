<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use function array_merge;
use function explode;
use function sprintf;
use function strpos;
class CoversHelper
{
    /**
     * Reflection provider.
     *
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * Gathers @covers and @coversDefaultClass annotations from phpdocs.
     *
     * @return array{PhpDocTagNode[], PhpDocTagNode[]}
     */
    public function getCoverAnnotations(?ResolvedPhpDocBlock $phpDoc) : array
    {
        if ($phpDoc === null) {
            return [[], []];
        }
        $phpDocNodes = $phpDoc->getPhpDocNodes();
        $covers = [];
        $coversDefaultClasses = [];
        foreach ($phpDocNodes as $docNode) {
            $covers = array_merge($covers, $docNode->getTagsByName('@covers'));
            $coversDefaultClasses = array_merge($coversDefaultClasses, $docNode->getTagsByName('@coversDefaultClass'));
        }
        return [$covers, $coversDefaultClasses];
    }
    /**
     * @return RuleError[] errors
     */
    public function processCovers(Node $node, PhpDocTagNode $phpDocTag, ?PhpDocTagNode $coversDefaultClass) : array
    {
        $errors = [];
        $covers = (string) $phpDocTag->value;
        $isMethod = strpos($covers, '::') !== \false;
        $fullName = $covers;
        if ($isMethod) {
            [$className, $method] = explode('::', $covers);
        } else {
            $className = $covers;
        }
        if ($className === '' && $node instanceof Node\Stmt\ClassMethod && $coversDefaultClass !== null) {
            $className = (string) $coversDefaultClass->value;
            $fullName = $className . $covers;
        }
        if ($this->reflectionProvider->hasClass($className)) {
            $class = $this->reflectionProvider->getClass($className);
            if (isset($method) && $method !== '' && !$class->hasMethod($method)) {
                $errors[] = RuleErrorBuilder::message(sprintf('@covers value %s references an invalid method.', $fullName))->build();
            }
        } else {
            if (!isset($method) && $this->reflectionProvider->hasFunction(new Name($covers, []), null)) {
                return $errors;
            }
            $errors[] = RuleErrorBuilder::message(sprintf('@covers value %s references an invalid %s.', $fullName, $isMethod ? 'method' : 'class or function'))->build();
        }
        return $errors;
    }
}

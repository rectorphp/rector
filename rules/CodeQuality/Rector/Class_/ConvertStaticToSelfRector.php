<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\ClassReflection;
use Rector\Configuration\Parameter\FeatureFlags;
use Rector\Enum\ObjectReference;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use ReflectionClassConstant;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/TQIcH
 * @see https://3v4l.org/VbcrN
 * @see https://3v4l.org/8Y0ba
 * @see https://3v4l.org/ZIeA1
 * @see https://phpstan.org/r/11d4c850-1a40-4fae-b665-291f96104d11
 * @see \Rector\Tests\CodeQuality\Rector\Class_\ConvertStaticToSelfRector\ConvertStaticToSelfRectorTest
 */
final class ConvertStaticToSelfRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `static::*` to `self::*` on final class or private static members', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        static::CONSTANT;
        static::$property;
        static::method();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        self::CONSTANT;
        self::$property;
        self::method();
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        $hasChanged = \false;
        $isFinal = $node->isFinal() || FeatureFlags::treatClassesAsFinal($node);
        $scope = ScopeFetcher::fetch($node);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $this->traverseNodesWithCallable($node->stmts, function (Node $subNode) use (&$hasChanged, $classReflection, $isFinal, $scope): ?Node {
            if (!$subNode instanceof StaticPropertyFetch && !$subNode instanceof StaticCall && !$subNode instanceof ClassConstFetch) {
                return null;
            }
            if ($this->shouldSkip($subNode, $classReflection, $isFinal, $scope)) {
                return null;
            }
            $hasChanged = \true;
            $subNode->class = new Name('self');
            return $subNode;
        });
        return $hasChanged ? $node : null;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticPropertyFetch|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\ClassConstFetch $node
     */
    private function shouldSkip($node, ClassReflection $classReflection, bool $isFinal, Scope $scope): bool
    {
        if (!$node->class instanceof Name) {
            return \true;
        }
        if (!$this->isName($node->class, ObjectReference::STATIC)) {
            return \true;
        }
        if (!$node->name instanceof Identifier) {
            return \true;
        }
        $name = (string) $this->getName($node->name);
        switch (\true) {
            case $node instanceof StaticPropertyFetch:
                $hasMember = $isFinal ? $classReflection->hasStaticProperty($name) : $classReflection->hasNativeProperty($name);
                break;
            case $node instanceof StaticCall:
                $hasMember = $isFinal ? $classReflection->hasMethod($name) : $classReflection->hasNativeMethod($name);
                break;
            case $node instanceof ClassConstFetch:
                $hasMember = $classReflection->hasConstant($name);
                break;
        }
        if (!$hasMember) {
            return \true;
        }
        switch (\true) {
            case $node instanceof StaticPropertyFetch:
                $reflection = $isFinal ? $classReflection->getStaticProperty($name) : $classReflection->getNativeProperty($name);
                break;
            case $node instanceof StaticCall:
                $reflection = $isFinal ? $classReflection->getMethod($name, $scope) : $classReflection->getNativeMethod($name);
                break;
            case $node instanceof ClassConstFetch:
                $reflection = $classReflection->getConstant($name);
                break;
        }
        // avoid overlapped change
        if (!$reflection->isStatic()) {
            return \true;
        }
        if (!$isFinal) {
            // init
            $memberIsFinal = \false;
            if ($reflection instanceof ClassConstantReflection) {
                // Get the native ReflectionClassConstant
                $declaringClass = $reflection->getDeclaringClass();
                $nativeReflectionClass = $declaringClass->getNativeReflection();
                $constantName = $reflection->getName();
                if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::FINAL_CLASS_CONSTANTS) && \PHP_VERSION_ID >= PhpVersionFeature::FINAL_CLASS_CONSTANTS) {
                    // PHP 8.1+
                    $nativeReflection = $nativeReflectionClass->getReflectionConstant($constantName);
                    $memberIsFinal = $nativeReflection instanceof ReflectionClassConstant && $nativeReflection->isFinal();
                }
            } else {
                $memberIsFinal = $reflection->isFinalByKeyword()->yes();
            }
            // Final native members can be safely converted
            if ($memberIsFinal) {
                return \false;
            }
            // Otherwise, only convert private native members
            return !$reflection->isPrivate();
        }
        // For final classes, can safely convert all members
        return \false;
    }
}

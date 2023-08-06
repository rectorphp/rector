<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ClassAnalyzer
{
    /**
     * @var string
     * @see https://regex101.com/r/FQH6RT/2
     */
    private const ANONYMOUS_CLASS_REGEX = '#^AnonymousClass\\w+$#';
    public function isAnonymousClassName(string $className) : bool
    {
        return StringUtils::isMatch($className, self::ANONYMOUS_CLASS_REGEX);
    }
    public function isAnonymousClass(Node $node) : bool
    {
        if (!$node instanceof Class_) {
            return $node instanceof New_ && $this->isAnonymousClass($node->class);
        }
        if ($node->isAnonymous()) {
            return \true;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection) {
            return $classReflection->isAnonymous();
        }
        return \false;
    }
}

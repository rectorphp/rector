<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\NodeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\ValueObject\MethodName;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\Nette\PhpDoc\Node\NetteInjectTagNode;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NetteInjectPropertyAnalyzer
{
    /**
     * @var ClassChildAnalyzer
     */
    private $classChildAnalyzer;

    public function __construct(ClassChildAnalyzer $classChildAnalyzer)
    {
        $this->classChildAnalyzer = $classChildAnalyzer;
    }

    public function detect(Property $property, PhpDocInfo $phpDocInfo): bool
    {
        if (! $phpDocInfo->hasByType(NetteInjectTagNode::class)) {
            return false;
        }

        /** @var Scope $scope */
        $scope = $property->getAttribute(AttributeKey::SCOPE);
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        if ($classReflection->isAbstract()) {
            return false;
        }

        if ($classReflection->isAnonymous()) {
            return false;
        }

        if ($this->classChildAnalyzer->hasChildClassMethod($classReflection, MethodName::CONSTRUCT)) {
            return false;
        }

        if ($this->classChildAnalyzer->hasParentClassMethod($classReflection, MethodName::CONSTRUCT)) {
            return false;
        }

        // it needs @var tag as well, to get the type
        if ($phpDocInfo->getVarTagValueNode() !== null) {
            return true;
        }

        return $property->type !== null;
    }
}

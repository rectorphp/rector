<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrors\RuleErrorWithMessageAndLineAndFile;
use Rector\Exception\NotImplementedException;
use ReflectionMethod;

final class KeepRectorNamespaceForRectorRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassLike::class;
    }

    /**
     * @param ClassLike $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $classLikeName =$node->name->toString();
        $scope->getNamespace

//            $ruleError = new RuleErrorWithMessageAndLineAndFile(
//                sprintf(
//                    'Change "%s()" method visibility to "%s" to respect parent method visibility.',
//                    $methodName,
//                    $methodVisibility
//                ),
//                $node->getLine(),
//                $scope->getFile()
//            );
//
//            return [$ruleError];
//        }
//
//        return [];
    }
}

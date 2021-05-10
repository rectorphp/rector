<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class SelfContainerFactory
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper
    ) {
    }

    public function createGetTypeMethodCall(ObjectType $objectType): MethodCall
    {
        $staticPropertyFetch = new StaticPropertyFetch(new Name('self'), 'container');
        $getMethodCall = new MethodCall($staticPropertyFetch, 'get');

        $className = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($objectType);
        if (! $className instanceof Name) {
            throw new ShouldNotHappenException();
        }

        $getMethodCall->args[] = new Arg(new ClassConstFetch($className, 'class'));

        return $getMethodCall;
    }
}

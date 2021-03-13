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
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
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

<?php

declare (strict_types=1);
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
    public function __construct(\Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function createGetTypeMethodCall(\PHPStan\Type\ObjectType $objectType) : \PhpParser\Node\Expr\MethodCall
    {
        $staticPropertyFetch = new \PhpParser\Node\Expr\StaticPropertyFetch(new \PhpParser\Node\Name('self'), 'container');
        $getMethodCall = new \PhpParser\Node\Expr\MethodCall($staticPropertyFetch, 'get');
        $className = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($objectType);
        if (!$className instanceof \PhpParser\Node\Name) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $getMethodCall->args[] = new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\ClassConstFetch($className, 'class'));
        return $getMethodCall;
    }
}

<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Naming;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\MethodCall;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class EventClassNaming
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(NodeNameResolver $nodeNameResolver, ClassNaming $classNaming)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classNaming = $classNaming;
    }

    public function getShortEventClassName(MethodCall $methodCall): string
    {
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($methodCall->name);

        /** @var string $className */
        $className = $methodCall->getAttribute(AttributeKey::CLASS_NAME);

        $shortClassName = $this->classNaming->getShortName($className);

        // "onMagic" => "Magic"
        $shortPropertyName = Strings::substring($methodName, strlen('on'));

        return $shortClassName . $shortPropertyName . 'Event';
    }

    public function resolveEventFileLocation(MethodCall $methodCall): string
    {
        $shortEventClassName = $this->getShortEventClassName($methodCall);

        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $methodCall->getAttribute(AttributeKey::FILE_INFO);

        return $fileInfo->getPath() . DIRECTORY_SEPARATOR . 'Event' . DIRECTORY_SEPARATOR . $shortEventClassName . '.php';
    }
}

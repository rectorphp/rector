<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class NameTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    public function getNodeClass(): string
    {
        return Name::class;
    }

    /**
     * @param Name $nameNode
     */
    public function resolve(Node $nameNode): ?string
    {
        /** @var Name|null $fqnName */
        $fqnName = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
        if ($fqnName instanceof Name) {
            $fullyQualifiedName = $fqnName->toString();

            $classLikeReflection = $this->smartClassReflector->reflect($fullyQualifiedName);
            dump($classLikeReflection);
            die;

            return $fullyQualifiedName;
        }

        return $nameNode->toString();
    }
}

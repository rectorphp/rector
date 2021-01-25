<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class DocAliasResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/cWpliJ/1
     */
    private const DOC_ALIAS_REGEX = '#\@(?<possible_alias>\w+)(\\\\)?#s';

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    /**
     * @return string[]
     */
    public function resolve(Node $node): array
    {
        $possibleDocAliases = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node, function (Node $node) use (
            &$possibleDocAliases
        ): void {
            $docComment = $node->getDocComment();
            if (! $docComment instanceof Doc) {
                return;
            }

            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
            $possibleDocAliases = $this->collectVarType($phpDocInfo, $possibleDocAliases);

            // e.g. "use Dotrine\ORM\Mapping as ORM" etc.
            $matches = Strings::matchAll($docComment->getText(), self::DOC_ALIAS_REGEX);
            foreach ($matches as $match) {
                $possibleDocAliases[] = $match['possible_alias'];
            }
        });

        return array_unique($possibleDocAliases);
    }

    /**
     * @param string[] $possibleDocAliases
     * @return string[]
     */
    private function collectVarType(PhpDocInfo $phpDocInfo, array $possibleDocAliases): array
    {
        $possibleDocAliases = $this->appendPossibleAliases($phpDocInfo->getVarType(), $possibleDocAliases);
        $possibleDocAliases = $this->appendPossibleAliases($phpDocInfo->getReturnType(), $possibleDocAliases);

        foreach ($phpDocInfo->getParamTypesByName() as $paramType) {
            $possibleDocAliases = $this->appendPossibleAliases($paramType, $possibleDocAliases);
        }

        return $possibleDocAliases;
    }

    /**
     * @param string[] $possibleDocAliases
     * @return string[]
     */
    private function appendPossibleAliases(Type $varType, array $possibleDocAliases): array
    {
        if ($varType instanceof AliasedObjectType) {
            $possibleDocAliases[] = $varType->getClassName();
        }

        if ($varType instanceof UnionType) {
            foreach ($varType->getTypes() as $type) {
                $possibleDocAliases = $this->appendPossibleAliases($type, $possibleDocAliases);
            }
        }

        return $possibleDocAliases;
    }
}

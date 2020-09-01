<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\OrderConstructorDependenciesByTypeAlphabeticallyRectorTest
 */
final class OrderConstructorDependenciesByTypeAlphabeticallyRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const SKIP_PATTERNS = '$skipPatterns';

    /**
     * @var string[]
     */
    private $skipPatterns = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Order __constructor dependencies by type A-Z', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function __construct(
        LatteToTwigConverter $latteToTwigConverter,
        SymfonyStyle $symfonyStyle,
        LatteAndTwigFinder $latteAndTwigFinder,
        SmartFileSystem $smartFileSystem
    ) {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function __construct(
        LatteAndTwigFinder $latteAndTwigFinder,
        LatteToTwigConverter $latteToTwigConverter,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle
    ) {
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $node->params = $this->getSortedParams($node);

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->skipPatterns = $configuration[self::SKIP_PATTERNS] ?? [];
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if (! $this->isName($classMethod, MethodName::CONSTRUCT)) {
            return true;
        }

        /** @var SmartFileInfo $smartFileInfo */
        $smartFileInfo = $classMethod->getAttribute(AttributeKey::FILE_INFO);
        if ($this->isFileInfoMatch($smartFileInfo)) {
            return true;
        }

        if ($classMethod->params === []) {
            return true;
        }

        if ($this->hasPrimitiveDataTypeParam($classMethod)) {
            return true;
        }

        return $this->hasParamWithNoType($classMethod);
    }

    /**
     * @return Param[]
     */
    private function getSortedParams(ClassMethod $classMethod): array
    {
        $params = $classMethod->getParams();
        usort($params, function (Param $firstParam, Param $secondParam): int {
            /** @var Name $firstParamType */
            $firstParamType = $this->getParamType($firstParam);
            /** @var Name $secondParamType */
            $secondParamType = $this->getParamType($secondParam);

            return $this->getShortName($firstParamType) <=> $this->getShortName($secondParamType);
        });

        return $params;
    }

    /**
     * Match file against matches, no patterns provided, then it matches
     */
    private function isFileInfoMatch(SmartFileInfo $smartFileInfo): bool
    {
        if ($this->skipPatterns === []) {
            return true;
        }

        foreach ($this->skipPatterns as $pattern) {
            if (fnmatch($pattern, $smartFileInfo->getRelativeFilePath(), FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }

    private function hasPrimitiveDataTypeParam(ClassMethod $classMethod): bool
    {
        foreach ($classMethod->params as $param) {
            if ($param->type instanceof Identifier) {
                return true;
            }
        }

        return false;
    }

    private function hasParamWithNoType(ClassMethod $classMethod): bool
    {
        foreach ($classMethod->params as $param) {
            if ($param->type === null) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Identifier|Name|UnionType|null
     */
    private function getParamType(Param $param)
    {
        return $param->type instanceof NullableType ? $param->type->type : $param->type;
    }
}

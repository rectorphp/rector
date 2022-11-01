<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use RectorPrefix202211\Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
final class PHPUnitDataProviderResolver
{
    /**
     * @see https://regex101.com/r/oZ9uYb/1
     *
     * @var string
     */
    private const DATA_PROVIDER_METHOD_NAME_REGEX = '#(?<method_name>\\w+)#m';
    /**
     * @var array<string, string[]>
     */
    private $dataProviderMethodsNamesByClassName = [];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return string[]
     */
    public function resolveDataProviderMethodNames(Class_ $class) : array
    {
        $className = $this->nodeNameResolver->getName($class);
        if (!\is_string($className)) {
            return [];
        }
        // cached
        if (isset($this->dataProviderMethodsNamesByClassName[$className])) {
            return $this->dataProviderMethodsNamesByClassName[$className];
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if (!$classReflection->isSubclassOf('PHPUnit\\Framework\\TestCase')) {
            return [];
        }
        $dataProviderMethodNames = $this->resolveDataProviderClassMethodNamesFromClass($class);
        $this->dataProviderMethodsNamesByClassName[$className] = $dataProviderMethodNames;
        return $dataProviderMethodNames;
    }
    /**
     * @return string[]
     */
    private function resolveDataProviderClassMethodNamesFromClass(Class_ $class) : array
    {
        $dataProviderMethodNames = [];
        // find data provider methods names :)
        foreach ($class->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($classMethod->isMagic()) {
                continue;
            }
            $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
            if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
                continue;
            }
            $currentDataProviderMethodNames = $this->resolveDataProviderMethodNamesFromPhpDocInfo($classMethodPhpDocInfo);
            $dataProviderMethodNames = \array_merge($dataProviderMethodNames, $currentDataProviderMethodNames);
        }
        return $dataProviderMethodNames;
    }
    /**
     * @return string[]
     */
    private function resolveDataProviderMethodNamesFromPhpDocInfo(PhpDocInfo $phpDocInfo) : array
    {
        $dataProviderMethodNames = [];
        /** @var PhpDocTagNode[] $dataProviderPhpDocTagNodes */
        $dataProviderPhpDocTagNodes = $phpDocInfo->getTagsByName('dataProvider');
        foreach ($dataProviderPhpDocTagNodes as $dataProviderPhpDocTagNode) {
            if (!$dataProviderPhpDocTagNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $rawMethodName = $dataProviderPhpDocTagNode->value->value;
            $match = Strings::match($rawMethodName, self::DATA_PROVIDER_METHOD_NAME_REGEX);
            if ($match === null) {
                continue;
            }
            $dataProviderMethodNames[] = $match['method_name'];
        }
        return $dataProviderMethodNames;
    }
}

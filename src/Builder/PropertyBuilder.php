<?php declare(strict_types=1);

namespace Rector\Builder;

use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property as PhpParserProperty;
use Rector\Builder\Class_\VariableInfo;
use Rector\Php\TypeAnalyzer;
use function Safe\sprintf;

/**
 * This class builds properties, and adds them to a class
 */
final class PropertyBuilder
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var StatementGlue
     */
    private $statementGlue;

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    public function __construct(
        BuilderFactory $builderFactory,
        StatementGlue $statementGlue,
        TypeAnalyzer $typeAnalyzer
    ) {
        $this->builderFactory = $builderFactory;
        $this->statementGlue = $statementGlue;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    public function addPropertyToClass(Class_ $classNode, VariableInfo $variableInfo): void
    {
        if ($this->doesPropertyAlreadyExist($classNode, $variableInfo)) {
            return;
        }

        $propertyNode = $this->buildPrivatePropertyNode($variableInfo);

        $this->statementGlue->addAsFirstMethod($classNode, $propertyNode);
    }

    private function doesPropertyAlreadyExist(Class_ $classNode, VariableInfo $variableInfo): bool
    {
        foreach ($classNode->stmts as $inClassNode) {
            if (! $inClassNode instanceof PhpParserProperty) {
                continue;
            }

            $classPropertyName = (string) $inClassNode->props[0]->name;

            if ($classPropertyName === $variableInfo->getName()) {
                return true;
            }
        }

        return false;
    }

    private function buildPrivatePropertyNode(VariableInfo $variableInfo): PhpParserProperty
    {
        $docComment = $this->createDocWithVarAnnotation($variableInfo->getTypes());

        $propertyBuilder = $this->builderFactory->property($variableInfo->getName())
            ->makePrivate()
            ->setDocComment($docComment);

        return $propertyBuilder->getNode();
    }

    /**
     * @param string[] $propertyTypes
     */
    private function createDocWithVarAnnotation(array $propertyTypes): Doc
    {
        // @todo resolve with PhpDocInfo
        return new Doc(sprintf('/**%s * @var %s%s */', PHP_EOL, $this->implodeTypes($propertyTypes), PHP_EOL));
    }

    /**
     * @param string[] $propertyTypes
     */
    private function implodeTypes(array $propertyTypes): string
    {
        $implodedTypes = '';
        foreach ($propertyTypes as $propertyType) {
            $implodedTypes .= $this->typeAnalyzer->isPhpReservedType($propertyType)
                ? $propertyType
                : '\\' . $propertyType . '|';
        }

        return rtrim($implodedTypes, '|');
    }
}

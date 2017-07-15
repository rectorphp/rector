<?php declare(strict_types=1);

namespace Rector\Reconstructor\DependencyInjection;

use PhpCsFixer\DocBlock\DocBlock;
use PhpParser\Builder\Method;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Parser;
use Rector\Contract\Dispatcher\ReconstructorInterface;

final class InjectAnnotationToConstructorReconstructor implements ReconstructorInterface
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(BuilderFactory $builderFactory, Parser $parser)
    {
        $this->builderFactory = $builderFactory;
        $this->parser = $parser;
    }

    public function isCandidate(Node $node): bool
    {
        return $node instanceof Class_;
    }

    /**
     * @param Class_|Node $classNode
     */
    public function reconstruct(Node $classNode): void
    {
        foreach ($classNode->stmts as $classElementStatement) {
            if (! $classElementStatement instanceof Property) {
                continue;
            }
            $propertyNode = $classElementStatement;

            $propertyDocBlock = $this->createDocBlock($propertyNode);
            $injectAnnotations = $propertyDocBlock->getAnnotationsOfType('inject');
            if (! $injectAnnotations) {
                continue;
            }

            $propertyType = $propertyDocBlock->getAnnotationsOfType('var')[0]->getTypes()[0];

            // 1. remove @inject annotation
            foreach ($injectAnnotations as $injectAnnotation) {
                $injectAnnotation->remove();
            }
            $propertyNode->setDocComment(new Doc($propertyDocBlock->getContent()));

            // 2. make public property private
            $propertyNode->flags = Class_::MODIFIER_PRIVATE;
            $propertyName = $propertyNode->props[0]->name;

            // build assignment for constructor method body
            /** @var Node[] $assign */
            $assign = $this->parser->parse(sprintf(
                '<?php $this->%s = $%s;',
                $propertyName,
                $propertyName
            ));

            $constructorMethod = $classNode->getMethod('__construct') ?: null;

            /** @var ClassMethod $constructorMethod */
            if ($constructorMethod) {
                $constructorMethod->params[] = $this->builderFactory->param($propertyName)
                    ->setTypeHint($propertyType)->getNode();

                $constructorMethod->stmts[] = $assign[0];
            } else {
                /** @var Method $constructorMethod */
                $constructorMethod = $this->builderFactory->method('__construct')
                    ->makePublic()
                    ->addParam($this->builderFactory->param($propertyName)
                        ->setTypeHint($propertyType)
                    )
                    ->addStmts($assign);

                $classNode->stmts[] = $constructorMethod->getNode();
            }
        }
    }

    private function createDocBlock(Property $propertyNode): DocBlock
    {
        return new DocBlock($propertyNode->getDocComment());
    }
}

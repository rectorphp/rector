<?php declare(strict_types=1);

namespace Rector\Reconstructor\DependencyInjection;

use Nette\Utils\Arrays;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Builder\ConstructorMethodBuilder;
use Rector\Contract\Dispatcher\ReconstructorInterface;
use Rector\Tests\Reconstructor\DependencyInjection\NamedServicesToConstructorReconstructor\Source\LocalKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

final class NamedServicesToConstructorReconstructor implements ReconstructorInterface
{
    /**
     * @var ConstructorMethodBuilder
     */
    private $constructorMethodBuilder;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(ConstructorMethodBuilder $constructorMethodBuilder, BuilderFactory $builderFactory)
    {
        $this->constructorMethodBuilder = $constructorMethodBuilder;
        $this->builderFactory = $builderFactory;
    }

    public function isCandidate(Node $node): bool
    {
        // @todo: limit to only 2 cases:
        // - SomeClass extends Controller
        // - SomeClass implements ContainerAwareInterface
        return $node instanceof Class_;
    }

    /**
     * @param Class_|Node $classNode
     */
    public function reconstruct(Node $classNode): void
    {
        foreach ($classNode->stmts as $classElementStatement) {
            // 1. Detect method
            if (! $classElementStatement instanceof ClassMethod) {
                continue;
            }

            $classMethodNode = $classElementStatement;

            foreach ($classMethodNode->stmts as $classMethodStatement) {
                // 2. Find ->get('...') call in it
                if (! $classMethodStatement instanceof MethodCall) {
                    continue;
                }

                $methodCallNode = $classMethodStatement;
                // A. Find ->get('...')->someCall()
                /**
                 * @todo: process also $var = $this->get('...');
                 * not a MethodCall on service, but Assign/PropertyFetch
                 */
                if (! $methodCallNode->var instanceof MethodCall) {
                    continue;
                }

                $methodCallNode = $methodCallNode->var;

                // 3. Accept only "$this->get()"
                if ($methodCallNode->name !== 'get') {
                    continue;
                }

                // 4. Accept only strings in "$this->get('string')"
                $argument = $methodCallNode->args[0]->value;
                if (! $methodCallNode->args[0]->value instanceof String_) {
                    continue;
                }

                /** @var String_ $argument */
                $serviceName = $argument->value;

                $container = $this->getContainerFromKernelClass();
                if (! $container->has($serviceName)) {
                    // service name could not be found
                    continue;
                }

                $service = $container->get($serviceName);

                // 6. Save Services
                $serviceType = get_class($service);
                $propertyName = $this->createPropertyNameFromClass($serviceType);
                $collectedServices[$propertyName] = $serviceType;

                // 7. Replace "$this->get()" => "$this->{$propertyName}"
                // A.

                // 7.1 Replace "$this" with "$this->propertyName"
                $methodCallNode->var = new PropertyFetch(
                    new Variable('this', [
                        'name' => $propertyName
                    ]), $propertyName
                );

                // 8. add this property to constructor
                $this->constructorMethodBuilder->addPropertyAssignToClass($classNode, $serviceType, $propertyName);

                // 9. add a property
                $propertyBuilder = $this->builderFactory->property($propertyName)
                    ->makePrivate()
                    ->setDocComment(new Doc('/**' . PHP_EOL . ' * @var ' . $serviceType . PHP_EOL . ' */'));

                $propertyNode = $propertyBuilder->getNode();
                $this->addProperty($classNode, $propertyNode);
            }
        }
    }

    /**
     * @todo extract to helper service, LocalKernelProvider::get...()
     */
    private function getContainerFromKernelClass(): ContainerInterface
    {
        /** @var Kernel $kernel */
        $kernel = new LocalKernel('dev', true);
        $kernel->boot();

        // @todo: initialize without creating cache or log directory
        // @todo: call only loadBundles() and initializeContainer() methods

        return $kernel->getContainer();
    }

    private function createPropertyNameFromClass(string $serviceType): string
    {
        $serviceNameParts = explode('\\', $serviceType);
        $lastNamePart = array_pop($serviceNameParts);

        return lcfirst($lastNamePart);
    }

    private function addProperty(Class_ $classNode, Property $propertyNode): void
    {
        foreach ($classNode->stmts as $key => $classElementNode) {
            if ($classElementNode instanceof Property || $classElementNode instanceof ClassMethod) {
                Arrays::insertBefore(
                    $classNode->stmts,
                    $key,
                    ['before_' . $key => $propertyNode]
                );

                return;
            }
        }

        $classNode->stmts[] = $propertyNode;
    }
}

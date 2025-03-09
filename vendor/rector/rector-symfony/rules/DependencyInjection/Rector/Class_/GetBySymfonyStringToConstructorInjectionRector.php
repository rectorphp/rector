<?php

declare (strict_types=1);
namespace Rector\Symfony\DependencyInjection\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\PHPStan\ScopeFetcher;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\Symfony\DependencyInjection\ThisGetTypeMatcher;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\DependencyInjection\Rector\Class_\GetBySymfonyStringToConstructorInjectionRector\GetBySymfonyStringToConstructorInjectionRectorTest
 */
final class GetBySymfonyStringToConstructorInjectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ClassDependencyManipulator $classDependencyManipulator;
    /**
     * @readonly
     */
    private ThisGetTypeMatcher $thisGetTypeMatcher;
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    /**
     * @var array<string, string>
     */
    private const SYMFONY_NAME_TO_TYPE_MAP = ['validator' => SymfonyClass::VALIDATOR_INTERFACE, 'event_dispatcher' => SymfonyClass::EVENT_DISPATCHER_INTERFACE, 'logger' => SymfonyClass::LOGGER_INTERFACE, 'jms_serializer' => SymfonyClass::JMS_SERIALIZER_INTERFACE, 'translator' => SymfonyClass::TRANSLATOR_INTERFACE, 'session' => SymfonyClass::SESSION_INTERFACRE, 'security.token_storage' => SymfonyClass::TOKEN_STORAGE_INTERFACE, 'router' => 'Symfony\\Component\\Routing\\RouterInterface', 'request_stack' => 'Symfony\\Component\\HttpFoundation\\RequestStack', 'http_kernel' => 'Symfony\\Component\\HttpKernel\\HttpKernelInterface', 'serializer' => 'Symfony\\Component\\Serializer\\SerializerInterface', 'security.authorization_checker' => 'Symfony\\Component\\Security\\Core\\Authorization\\AuthorizationCheckerInterface', 'templating' => 'Symfony\\Component\\Templating\\EngineInterface', 'twig' => 'Twig\\Environment', 'doctrine' => 'Doctrine\\Persistence\\ManagerRegistry', 'form.factory' => 'Symfony\\Component\\Form\\FormFactoryInterface', 'security.csrf.token_manager' => 'Symfony\\Component\\Security\\Core\\Authorization\\CsrfTokenManagerInterface', 'parameter_bag' => 'Symfony\\Component\\DependencyInjection\\ParameterBag\\ContainerBagInterface', 'message_bus' => 'Symfony\\Component\\Messenger\\MessageBusInterface', 'messenger.default_bus' => 'Symfony\\Component\\Messenger\\MessageBusInterface'];
    public function __construct(ClassDependencyManipulator $classDependencyManipulator, ThisGetTypeMatcher $thisGetTypeMatcher, PropertyNaming $propertyNaming)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->thisGetTypeMatcher = $thisGetTypeMatcher;
        $this->propertyNaming = $propertyNaming;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Converts typical Symfony services like $this->get("validator") in commands/controllers to constructor injection (step 3/x)', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function someMethod()
    {
        $someType = $this->get('validator');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SomeController extends Controller
{
    public function __construct(private ValidatorInterface $validator)

    public function someMethod()
    {
        $someType = $this->validator;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $propertyMetadatas = [];
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$propertyMetadatas) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            $serviceName = $this->thisGetTypeMatcher->matchString($node);
            if (!\is_string($serviceName)) {
                return null;
            }
            $serviceType = self::SYMFONY_NAME_TO_TYPE_MAP[$serviceName] ?? null;
            if ($serviceType === null) {
                return null;
            }
            $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);
            $propertyMetadata = new PropertyMetadata($propertyName, new FullyQualifiedObjectType($serviceType));
            $propertyMetadatas[] = $propertyMetadata;
            return $this->nodeFactory->createPropertyFetch('this', $propertyMetadata->getName());
        });
        if ($propertyMetadatas === []) {
            return null;
        }
        foreach ($propertyMetadatas as $propertyMetadata) {
            $this->classDependencyManipulator->addConstructorDependency($node, $propertyMetadata);
        }
        return $node;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        $scope = ScopeFetcher::fetch($class);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        if ($classReflection->is(SymfonyClass::CONTAINER_AWARE_COMMAND)) {
            return \false;
        }
        return !$classReflection->is(SymfonyClass::CONTROLLER);
    }
}

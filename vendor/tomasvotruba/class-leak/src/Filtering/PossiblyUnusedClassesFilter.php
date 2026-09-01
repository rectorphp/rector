<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\ClassLeak\Filtering;

use RectorPrefix202609\TomasVotruba\ClassLeak\ValueObject\FileWithClass;
use RectorPrefix202609\Webmozart\Assert\Assert;
final class PossiblyUnusedClassesFilter
{
    /**
     * These class types are used by some kind of collector pattern. Either loaded magically, registered only in config,
     * an entry point or a tagged extensions.
     *
     * @var string[]
     */
    private const DEFAULT_TYPES_TO_SKIP = [
        // http-kernel
        'RectorPrefix202609\Symfony\Component\Console\Application',
        'RectorPrefix202609\Symfony\Component\HttpKernel\DependencyInjection\Extension',
        'RectorPrefix202609\Symfony\Bundle\FrameworkBundle\Controller\Controller',
        'RectorPrefix202609\Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
        'RectorPrefix202609\Livewire\Component',
        'RectorPrefix202609\Illuminate\Routing\Controller',
        'RectorPrefix202609\Illuminate\Contracts\Http\Kernel',
        'RectorPrefix202609\Illuminate\Support\ServiceProvider',
        // events
        'RectorPrefix202609\Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'RectorPrefix202609\Symfony\Component\Form\FormTypeExtensionInterface',
        'RectorPrefix202609\Symfony\Component\Security\Core\Authentication\SimpleAuthenticatorInterface',
        'RectorPrefix202609\Vich\UploaderBundle\Naming\DirectoryNamerInterface',
        // validator
        'RectorPrefix202609\Symfony\Component\Validator\Constraint',
        'RectorPrefix202609\Symfony\Component\Validator\ConstraintValidator',
        'RectorPrefix202609\Symfony\Component\Validator\ConstraintValidatorInterface',
        'RectorPrefix202609\Symfony\Component\Security\Core\Authorization\Voter\VoterInterface',
        'RectorPrefix202609\Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface',
        'RectorPrefix202609\Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface',
        'RectorPrefix202609\Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface',
        'RectorPrefix202609\Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface',
        // symfony forms
        'RectorPrefix202609\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface',
        'RectorPrefix202609\Symfony\Component\Form\AbstractType',
        // doctrine
        'RectorPrefix202609\Doctrine\Common\DataFixtures\FixtureInterface',
        'RectorPrefix202609\Doctrine\Common\EventSubscriber',
        'RectorPrefix202609\Nelmio\Alice\ProcessorInterface',
        // kernel
        'RectorPrefix202609\Symfony\Component\HttpKernel\Bundle\BundleInterface',
        'RectorPrefix202609\Symfony\Component\HttpKernel\KernelInterface',
        'RectorPrefix202609\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
        // console
        'RectorPrefix202609\Symfony\Component\Console\Command\Command',
        'RectorPrefix202609\Entropy\Console\Contract\CommandInterface',
        'RectorPrefix202609\Twig\Extension\ExtensionInterface',
        'RectorPrefix202609\PhpCsFixer\Fixer\FixerInterface',
        'RectorPrefix202609\PHPUnit\Framework\TestCase',
        'PHPStan\Rules\Rule',
        'PHPStan\Command\ErrorFormatter\ErrorFormatter',
        // tests
        'RectorPrefix202609\Behat\Behat\Context\Context',
        // jms
        'RectorPrefix202609\JMS\Serializer\Handler\SubscribingHandlerInterface',
        // laravel
        'RectorPrefix202609\Illuminate\Support\ServiceProvider',
        'RectorPrefix202609\Illuminate\Foundation\Http\Kernel',
        'RectorPrefix202609\Illuminate\Contracts\Console\Kernel',
        'RectorPrefix202609\Illuminate\Routing\Controller',
        // Doctrine
        'RectorPrefix202609\Doctrine\Migrations\AbstractMigration',
    ];
    /**
     * @var string[]
     */
    private const DEFAULT_ATTRIBUTES_TO_SKIP = [
        // Symfony
        'RectorPrefix202609\Symfony\Component\Console\Attribute\AsCommand',
        'RectorPrefix202609\Symfony\Component\HttpKernel\Attribute\AsController',
        'RectorPrefix202609\Symfony\Component\EventDispatcher\Attribute\AsEventListener',
    ];
    /**
     * @param FileWithClass[] $filesWithClasses
     * @param string[] $usedClassNames
     * @param string[] $typesToSkip
     * @param string[] $suffixesToSkip
     * @param string[] $attributesToSkip
     *
     * @return FileWithClass[]
     */
    public function filter(array $filesWithClasses, array $usedClassNames, array $typesToSkip, array $suffixesToSkip, array $attributesToSkip, bool $shouldIncludeEntities): array
    {
        Assert::allString($usedClassNames);
        Assert::allString($typesToSkip);
        Assert::allString($suffixesToSkip);
        $possiblyUnusedFilesWithClasses = [];
        $typesToSkip = array_merge($typesToSkip, self::DEFAULT_TYPES_TO_SKIP);
        $attributesToSkip = array_merge($attributesToSkip, self::DEFAULT_ATTRIBUTES_TO_SKIP);
        foreach ($filesWithClasses as $fileWithClass) {
            if (in_array($fileWithClass->getClassName(), $usedClassNames, \true)) {
                continue;
            }
            // is excluded interfaces?
            if ($this->shouldSkip($fileWithClass->getClassName(), $typesToSkip)) {
                continue;
            }
            if ($shouldIncludeEntities === \false && $fileWithClass->isEntity()) {
                continue;
            }
            if ($fileWithClass->isSerialized()) {
                continue;
            }
            // is excluded suffix?
            foreach ($suffixesToSkip as $suffixToSkip) {
                if (substr_compare($fileWithClass->getClassName(), $suffixToSkip, -strlen($suffixToSkip)) === 0) {
                    continue 2;
                }
            }
            // is excluded attributes?
            foreach ($fileWithClass->getAttributes() as $attribute) {
                if ($this->shouldSkip($attribute, $attributesToSkip)) {
                    continue 2;
                }
            }
            $possiblyUnusedFilesWithClasses[] = $fileWithClass;
        }
        return $possiblyUnusedFilesWithClasses;
    }
    /**
     * @param string[] $skips
     */
    private function shouldSkip(string $type, array $skips): bool
    {
        foreach ($skips as $skip) {
            if (strpos($type, '*') === \false && is_a($type, $skip, \true)) {
                return \true;
            }
            if (fnmatch($skip, $type, \FNM_NOESCAPE)) {
                return \true;
            }
        }
        return \false;
    }
}

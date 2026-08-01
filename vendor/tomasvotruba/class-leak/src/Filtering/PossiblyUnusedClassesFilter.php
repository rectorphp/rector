<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\ClassLeak\Filtering;

use RectorPrefix202608\TomasVotruba\ClassLeak\ValueObject\FileWithClass;
use RectorPrefix202608\Webmozart\Assert\Assert;
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
        'RectorPrefix202608\Symfony\Component\Console\Application',
        'RectorPrefix202608\Symfony\Component\HttpKernel\DependencyInjection\Extension',
        'RectorPrefix202608\Symfony\Bundle\FrameworkBundle\Controller\Controller',
        'RectorPrefix202608\Symfony\Bundle\FrameworkBundle\Controller\AbstractController',
        'RectorPrefix202608\Livewire\Component',
        'RectorPrefix202608\Illuminate\Routing\Controller',
        'RectorPrefix202608\Illuminate\Contracts\Http\Kernel',
        'RectorPrefix202608\Illuminate\Support\ServiceProvider',
        // events
        'RectorPrefix202608\Symfony\Component\EventDispatcher\EventSubscriberInterface',
        'RectorPrefix202608\Symfony\Component\Form\FormTypeExtensionInterface',
        'RectorPrefix202608\Symfony\Component\Security\Core\Authentication\SimpleAuthenticatorInterface',
        'RectorPrefix202608\Vich\UploaderBundle\Naming\DirectoryNamerInterface',
        // validator
        'RectorPrefix202608\Symfony\Component\Validator\Constraint',
        'RectorPrefix202608\Symfony\Component\Validator\ConstraintValidator',
        'RectorPrefix202608\Symfony\Component\Validator\ConstraintValidatorInterface',
        'RectorPrefix202608\Symfony\Component\Security\Core\Authorization\Voter\VoterInterface',
        'RectorPrefix202608\Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface',
        'RectorPrefix202608\Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface',
        'RectorPrefix202608\Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface',
        'RectorPrefix202608\Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface',
        // symfony forms
        'RectorPrefix202608\Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface',
        'RectorPrefix202608\Symfony\Component\Form\AbstractType',
        // doctrine
        'RectorPrefix202608\Doctrine\Common\DataFixtures\FixtureInterface',
        'RectorPrefix202608\Doctrine\Common\EventSubscriber',
        'RectorPrefix202608\Nelmio\Alice\ProcessorInterface',
        // kernel
        'RectorPrefix202608\Symfony\Component\HttpKernel\Bundle\BundleInterface',
        'RectorPrefix202608\Symfony\Component\HttpKernel\KernelInterface',
        'RectorPrefix202608\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
        // console
        'RectorPrefix202608\Symfony\Component\Console\Command\Command',
        'RectorPrefix202608\Entropy\Console\Contract\CommandInterface',
        'RectorPrefix202608\Twig\Extension\ExtensionInterface',
        'RectorPrefix202608\PhpCsFixer\Fixer\FixerInterface',
        'RectorPrefix202608\PHPUnit\Framework\TestCase',
        'PHPStan\Rules\Rule',
        'PHPStan\Command\ErrorFormatter\ErrorFormatter',
        // tests
        'RectorPrefix202608\Behat\Behat\Context\Context',
        // jms
        'RectorPrefix202608\JMS\Serializer\Handler\SubscribingHandlerInterface',
        // laravel
        'RectorPrefix202608\Illuminate\Support\ServiceProvider',
        'RectorPrefix202608\Illuminate\Foundation\Http\Kernel',
        'RectorPrefix202608\Illuminate\Contracts\Console\Kernel',
        'RectorPrefix202608\Illuminate\Routing\Controller',
        // Doctrine
        'RectorPrefix202608\Doctrine\Migrations\AbstractMigration',
    ];
    /**
     * @var string[]
     */
    private const DEFAULT_ATTRIBUTES_TO_SKIP = [
        // Symfony
        'RectorPrefix202608\Symfony\Component\Console\Attribute\AsCommand',
        'RectorPrefix202608\Symfony\Component\HttpKernel\Attribute\AsController',
        'RectorPrefix202608\Symfony\Component\EventDispatcher\Attribute\AsEventListener',
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

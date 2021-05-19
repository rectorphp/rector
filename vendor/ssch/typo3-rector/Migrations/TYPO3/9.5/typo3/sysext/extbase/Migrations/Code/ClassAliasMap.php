<?php

namespace RectorPrefix20210519;

return [
    // TYPO3 v8 replacements
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Service\\TypoScriptService' => \RectorPrefix20210519\TYPO3\CMS\Core\TypoScript\TypoScriptService::class,
    // TYPO3 v9 replacements
    // Configuration
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Configuration\\Exception\\ContainerIsLockedException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Configuration\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Configuration\\Exception\\NoSuchFileException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Configuration\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Configuration\\Exception\\NoSuchOptionException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Configuration\Exception::class,
    // no proper fallback
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidMarkerException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidRequestTypeException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Mvc\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\RequiredArgumentMissingException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Mvc\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidCommandIdentifierException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Mvc\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidOrNoRequestHashException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Security\Exception\InvalidHashException::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidUriPatternException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Security\Exception::class,
    // Object Container
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Container\\Exception\\CannotInitializeCacheException' => \RectorPrefix20210519\TYPO3\CMS\Core\Cache\Exception\InvalidCacheException::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Container\\Exception\\TooManyRecursionLevelsException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Object\Exception::class,
    // ObjectManager
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\Exception\\WrongScopeException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Object\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\InvalidClassException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Object\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\InvalidObjectConfigurationException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Object\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\InvalidObjectException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Object\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\ObjectAlreadyRegisteredException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Object\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\UnknownClassException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Object\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\UnknownInterfaceException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Object\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Object\\UnresolvedDependenciesException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Object\Exception::class,
    // Persistence
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception\\CleanStateNotMemorizedException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Persistence\Generic\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception\\InvalidPropertyTypeException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Persistence\Generic\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Exception\\MissingBackendException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Persistence\Generic\Exception::class,
    // Property
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Property\\Exception\\FormatNotSupportedException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Property\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Property\\Exception\\InvalidFormatException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Property\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Property\\Exception\\InvalidPropertyException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Property\Exception::class,
    // Reflection
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Reflection\\Exception\\InvalidPropertyTypeException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Reflection\Exception::class,
    // Security
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Security\\Exception\\InvalidArgumentForRequestHashGenerationException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Security\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Security\\Exception\\SyntacticallyWrongRequestHashException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Security\Exception::class,
    // Validation
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Validation\\Exception\\InvalidSubjectException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Validation\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Validation\\Exception\\NoValidatorFoundException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Validation\Exception::class,
    // Fluid
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidViewHelperException' => \RectorPrefix20210519\TYPO3\CMS\Extbase\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Mvc\\Exception\\InvalidTemplateResourceException' => \RectorPrefix20210519\TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException::class,
    // Service
    'RectorPrefix20210519\\TYPO3\\CMS\\Extbase\\Service\\FlexFormService' => \RectorPrefix20210519\TYPO3\CMS\Core\Service\FlexFormService::class,
];

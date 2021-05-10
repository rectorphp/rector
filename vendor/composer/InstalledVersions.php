<?php

namespace RectorPrefix20210510\Composer;

use RectorPrefix20210510\Composer\Autoload\ClassLoader;
use RectorPrefix20210510\Composer\Semver\VersionParser;
class InstalledVersions
{
    private static $installed = array('root' => array('pretty_version' => 'dev-main', 'version' => 'dev-main', 'aliases' => array(0 => '0.10.x-dev'), 'reference' => NULL, 'name' => 'rector/rector-src'), 'versions' => array('composer/package-versions-deprecated' => array('pretty_version' => '1.11.99.1', 'version' => '1.11.99.1', 'aliases' => array(), 'reference' => '7413f0b55a051e89485c5cb9f765fe24bb02a7b6'), 'composer/semver' => array('pretty_version' => '3.2.4', 'version' => '3.2.4.0', 'aliases' => array(), 'reference' => 'a02fdf930a3c1c3ed3a49b5f63859c0c20e10464'), 'composer/xdebug-handler' => array('pretty_version' => '2.0.1', 'version' => '2.0.1.0', 'aliases' => array(), 'reference' => '964adcdd3a28bf9ed5d9ac6450064e0d71ed7496'), 'danielstjules/stringy' => array('pretty_version' => '3.1.0', 'version' => '3.1.0.0', 'aliases' => array(), 'reference' => 'df24ab62d2d8213bbbe88cc36fc35a4503b4bd7e'), 'doctrine/inflector' => array('pretty_version' => '2.0.3', 'version' => '2.0.3.0', 'aliases' => array(), 'reference' => '9cf661f4eb38f7c881cac67c75ea9b00bf97b210'), 'ergebnis/json-printer' => array('pretty_version' => '3.1.1', 'version' => '3.1.1.0', 'aliases' => array(), 'reference' => 'e4190dadd9937a77d8afcaf2b6c42a528ab367d6'), 'idiosyncratic/editorconfig' => array('pretty_version' => '0.1.1', 'version' => '0.1.1.0', 'aliases' => array(), 'reference' => '50f742daee8b7a632b795f5927d8d88c43dd3a4f'), 'jean85/pretty-package-versions' => array('pretty_version' => '1.6.0', 'version' => '1.6.0.0', 'aliases' => array(), 'reference' => '1e0104b46f045868f11942aea058cd7186d6c303'), 'nette/caching' => array('pretty_version' => 'v3.1.1', 'version' => '3.1.1.0', 'aliases' => array(), 'reference' => '3e771c589dee414724be473c24ad16dae50c1960'), 'nette/finder' => array('pretty_version' => 'v2.5.2', 'version' => '2.5.2.0', 'aliases' => array(), 'reference' => '4ad2c298eb8c687dd0e74ae84206a4186eeaed50'), 'nette/neon' => array('pretty_version' => 'v3.2.2', 'version' => '3.2.2.0', 'aliases' => array(), 'reference' => 'e4ca6f4669121ca6876b1d048c612480e39a28d5'), 'nette/utils' => array('pretty_version' => 'v3.2.2', 'version' => '3.2.2.0', 'aliases' => array(), 'reference' => '967cfc4f9a1acd5f1058d76715a424c53343c20c'), 'nikic/php-parser' => array('pretty_version' => 'v4.10.4', 'version' => '4.10.4.0', 'aliases' => array(), 'reference' => 'c6d052fc58cb876152f89f532b95a8d7907e7f0e'), 'ocramius/package-versions' => array('replaced' => array(0 => '1.11.99')), 'phpstan/phpdoc-parser' => array('pretty_version' => '0.5.4', 'version' => '0.5.4.0', 'aliases' => array(), 'reference' => 'e352d065af1ae9b41c12d1dfd309e90f7b1f55c9'), 'phpstan/phpstan' => array('pretty_version' => '0.12.85', 'version' => '0.12.85.0', 'aliases' => array(), 'reference' => '20e6333c0067875ad7697cd8acdf245c6ef69d03'), 'phpstan/phpstan-phpunit' => array('pretty_version' => '0.12.18', 'version' => '0.12.18.0', 'aliases' => array(), 'reference' => 'ab44aec7cfb5cb267b8bc30a8caea86dd50d1f72'), 'psr/container' => array('pretty_version' => '1.1.1', 'version' => '1.1.1.0', 'aliases' => array(), 'reference' => '8622567409010282b7aeebe4bb841fe98b58dcaf'), 'psr/container-implementation' => array('provided' => array(0 => '1.0')), 'psr/event-dispatcher' => array('pretty_version' => '1.0.0', 'version' => '1.0.0.0', 'aliases' => array(), 'reference' => 'dbefd12671e8a14ec7f180cab83036ed26714bb0'), 'psr/event-dispatcher-implementation' => array('provided' => array(0 => '1.0')), 'psr/log' => array('pretty_version' => '1.1.4', 'version' => '1.1.4.0', 'aliases' => array(), 'reference' => 'd49695b909c3b7628b6289db5479a1c204601f11'), 'psr/log-implementation' => array('provided' => array(0 => '1.0')), 'rector/extension-installer' => array('pretty_version' => '0.10.2', 'version' => '0.10.2.0', 'aliases' => array(), 'reference' => '56c97630fca170b5586b2f08e76348f924ebb8dd'), 'rector/rector' => array('replaced' => array(0 => '0.10.x-dev', 1 => 'dev-main')), 'rector/rector-cakephp' => array('pretty_version' => '0.10.4', 'version' => '0.10.4.0', 'aliases' => array(), 'reference' => '5155ac30d2cd04144690c118a030af0a4a1e68f4'), 'rector/rector-doctrine' => array('pretty_version' => '0.10.6', 'version' => '0.10.6.0', 'aliases' => array(), 'reference' => '52dbdf1d60c522c1c40142a194960610db451f91'), 'rector/rector-laravel' => array('pretty_version' => '0.10.2', 'version' => '0.10.2.0', 'aliases' => array(), 'reference' => 'd4ccd50d7975bea9a0ce961bbaded385b5ede739'), 'rector/rector-nette' => array('pretty_version' => '0.10.10', 'version' => '0.10.10.0', 'aliases' => array(), 'reference' => 'e9f0de1ffb3ba1eecdaa281fcd64fda1d3515e6f'), 'rector/rector-nette-to-symfony' => array('pretty_version' => '0.10.0', 'version' => '0.10.0.0', 'aliases' => array(), 'reference' => '27be9cb982ac7ad3799e0ac7fd45be0a46d7fb0b'), 'rector/rector-phpunit' => array('pretty_version' => '0.10.8', 'version' => '0.10.8.0', 'aliases' => array(), 'reference' => '2b272b61cacf29bf0e448719165c8376cd41872e'), 'rector/rector-src' => array('pretty_version' => 'dev-main', 'version' => 'dev-main', 'aliases' => array(0 => '0.10.x-dev'), 'reference' => NULL), 'rector/rector-symfony' => array('pretty_version' => '0.10.6', 'version' => '0.10.6.0', 'aliases' => array(), 'reference' => '583e77ad2fc8c7740418d55884beb3d18822e115'), 'sebastian/diff' => array('pretty_version' => '4.0.4', 'version' => '4.0.4.0', 'aliases' => array(), 'reference' => '3461e3fccc7cfdfc2720be910d3bd73c69be590d'), 'shanethehat/pretty-xml' => array('pretty_version' => '1.0.2', 'version' => '1.0.2.0', 'aliases' => array(), 'reference' => '2b063c6544c8dc9563c53cb72eb06d1d74c9e75f'), 'symfony/config' => array('pretty_version' => 'v5.2.7', 'version' => '5.2.7.0', 'aliases' => array(), 'reference' => '3817662ada105c8c4d1afdb4ec003003efd1d8d8'), 'symfony/console' => array('pretty_version' => 'v5.2.7', 'version' => '5.2.7.0', 'aliases' => array(), 'reference' => '90374b8ed059325b49a29b55b3f8bb4062c87629'), 'symfony/dependency-injection' => array('pretty_version' => 'v5.2.7', 'version' => '5.2.7.0', 'aliases' => array(), 'reference' => '6ca378b99e3c9ba6127eb43b68389fb2b7348577'), 'symfony/deprecation-contracts' => array('pretty_version' => 'v2.4.0', 'version' => '2.4.0.0', 'aliases' => array(), 'reference' => '5f38c8804a9e97d23e0c8d63341088cd8a22d627'), 'symfony/error-handler' => array('pretty_version' => 'v5.2.7', 'version' => '5.2.7.0', 'aliases' => array(), 'reference' => 'ea3ddbf67615e883ca7c33a4de61213789846782'), 'symfony/event-dispatcher' => array('pretty_version' => 'v5.2.4', 'version' => '5.2.4.0', 'aliases' => array(), 'reference' => 'd08d6ec121a425897951900ab692b612a61d6240'), 'symfony/event-dispatcher-contracts' => array('pretty_version' => 'v2.4.0', 'version' => '2.4.0.0', 'aliases' => array(), 'reference' => '69fee1ad2332a7cbab3aca13591953da9cdb7a11'), 'symfony/event-dispatcher-implementation' => array('provided' => array(0 => '2.0')), 'symfony/filesystem' => array('pretty_version' => 'v5.2.7', 'version' => '5.2.7.0', 'aliases' => array(), 'reference' => '056e92acc21d977c37e6ea8e97374b2a6c8551b0'), 'symfony/finder' => array('pretty_version' => 'v5.2.4', 'version' => '5.2.4.0', 'aliases' => array(), 'reference' => '0d639a0943822626290d169965804f79400e6a04'), 'symfony/http-client-contracts' => array('pretty_version' => 'v2.4.0', 'version' => '2.4.0.0', 'aliases' => array(), 'reference' => '7e82f6084d7cae521a75ef2cb5c9457bbda785f4'), 'symfony/http-foundation' => array('pretty_version' => 'v5.2.7', 'version' => '5.2.7.0', 'aliases' => array(), 'reference' => 'a416487a73bb9c9d120e9ba3a60547f4a3fb7a1f'), 'symfony/http-kernel' => array('pretty_version' => 'v5.2.7', 'version' => '5.2.7.0', 'aliases' => array(), 'reference' => '1e9f6879f070f718e0055fbac232a56f67b8b6bd'), 'symfony/polyfill-ctype' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => 'c6c942b1ac76c82448322025e084cadc56048b4e'), 'symfony/polyfill-intl-grapheme' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => '5601e09b69f26c1828b13b6bb87cb07cddba3170'), 'symfony/polyfill-intl-normalizer' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => '43a0283138253ed1d48d352ab6d0bdb3f809f248'), 'symfony/polyfill-mbstring' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => '5232de97ee3b75b0360528dae24e73db49566ab1'), 'symfony/polyfill-php73' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => 'a678b42e92f86eca04b7fa4c0f6f19d097fb69e2'), 'symfony/polyfill-php80' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => 'dc3063ba22c2a1fd2f45ed856374d79114998f91'), 'symfony/service-contracts' => array('pretty_version' => 'v2.4.0', 'version' => '2.4.0.0', 'aliases' => array(), 'reference' => 'f040a30e04b57fbcc9c6cbcf4dbaa96bd318b9bb'), 'symfony/service-implementation' => array('provided' => array(0 => '1.0|2.0')), 'symfony/string' => array('pretty_version' => 'v5.2.6', 'version' => '5.2.6.0', 'aliases' => array(), 'reference' => 'ad0bd91bce2054103f5eaa18ebeba8d3bc2a0572'), 'symfony/var-dumper' => array('pretty_version' => 'v5.2.7', 'version' => '5.2.7.0', 'aliases' => array(), 'reference' => '27cb9f7cfa3853c736425c7233a8f68814b19636'), 'symplify/astral' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '4347ac8b82c7abc46007850147bdd48f2dd9f511'), 'symplify/autowire-array-parameter' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '130fff4f734e752dee5d14da26038e9c213d1381'), 'symplify/composer-json-manipulator' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '3669e146cb16d990cb33c1dcd321e462aeb63209'), 'symplify/console-color-diff' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '4be4c9a5951bc4e06138099582143ce965f4e63c'), 'symplify/console-package-builder' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '6b3c83944b8c094c5a4b80356316d2348a9056c7'), 'symplify/easy-testing' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '0bde7c1c45d80a6ff9587a0b960984d9dc9b9700'), 'symplify/package-builder' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '126c7b9e3c13fb44c581fced4c5355bd51466d55'), 'symplify/rule-doc-generator-contracts' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '0f04552a1fad5beff718935086177ef2a4de5ca6'), 'symplify/set-config-resolver' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '00a96e094b2c4e784b74797a07a2ca562d14a3b3'), 'symplify/simple-php-doc-parser' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => 'c3e9dfc3a33dc2d16c3beff7fefa27a3b4cf421b'), 'symplify/skipper' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => 'cd166ddf714cfdc120fb44bc1338865b2f59b889'), 'symplify/smart-file-system' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '2721d9eb56b43726307d42a4b64f2cee5b2534b5'), 'symplify/symfony-php-config' => array('pretty_version' => 'v9.3.0', 'version' => '9.3.0.0', 'aliases' => array(), 'reference' => '0a5bf9f1d7d970f2df2b4e4225c297bd774c23c8'), 'symplify/symplify-kernel' => array('pretty_version' => 'v9.3.1', 'version' => '9.3.1.0', 'aliases' => array(), 'reference' => '4f895a22b50579ad0767f6b18f2e0aaf8b11fcac'), 'tracy/tracy' => array('pretty_version' => 'v2.8.4', 'version' => '2.8.4.0', 'aliases' => array(), 'reference' => 'cb7d3dcd9469aa2aa6722edf6bee2d5d75188079'), 'webmozart/assert' => array('pretty_version' => '1.10.0', 'version' => '1.10.0.0', 'aliases' => array(), 'reference' => '6964c76c7804814a842473e0c8fd15bab0f18e25')));
    private static $canGetVendors;
    private static $installedByVendor = array();
    public static function getInstalledPackages()
    {
        $packages = array();
        foreach (self::getInstalled() as $installed) {
            $packages[] = \array_keys($installed['versions']);
        }
        if (1 === \count($packages)) {
            return $packages[0];
        }
        return \array_keys(\array_flip(\call_user_func_array('array_merge', $packages)));
    }
    public static function isInstalled($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (isset($installed['versions'][$packageName])) {
                return \true;
            }
        }
        return \false;
    }
    public static function satisfies(VersionParser $parser, $packageName, $constraint)
    {
        $constraint = $parser->parseConstraints($constraint);
        $provided = $parser->parseConstraints(self::getVersionRanges($packageName));
        return $provided->matches($constraint);
    }
    public static function getVersionRanges($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            $ranges = array();
            if (isset($installed['versions'][$packageName]['pretty_version'])) {
                $ranges[] = $installed['versions'][$packageName]['pretty_version'];
            }
            if (\array_key_exists('aliases', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['aliases']);
            }
            if (\array_key_exists('replaced', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['replaced']);
            }
            if (\array_key_exists('provided', $installed['versions'][$packageName])) {
                $ranges = \array_merge($ranges, $installed['versions'][$packageName]['provided']);
            }
            return \implode(' || ', $ranges);
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getVersion($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['version'])) {
                return null;
            }
            return $installed['versions'][$packageName]['version'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getPrettyVersion($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['pretty_version'])) {
                return null;
            }
            return $installed['versions'][$packageName]['pretty_version'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getReference($packageName)
    {
        foreach (self::getInstalled() as $installed) {
            if (!isset($installed['versions'][$packageName])) {
                continue;
            }
            if (!isset($installed['versions'][$packageName]['reference'])) {
                return null;
            }
            return $installed['versions'][$packageName]['reference'];
        }
        throw new \OutOfBoundsException('Package "' . $packageName . '" is not installed');
    }
    public static function getRootPackage()
    {
        $installed = self::getInstalled();
        return $installed[0]['root'];
    }
    public static function getRawData()
    {
        return self::$installed;
    }
    public static function reload($data)
    {
        self::$installed = $data;
        self::$installedByVendor = array();
    }
    private static function getInstalled()
    {
        if (null === self::$canGetVendors) {
            self::$canGetVendors = \method_exists('RectorPrefix20210510\\Composer\\Autoload\\ClassLoader', 'getRegisteredLoaders');
        }
        $installed = array();
        if (self::$canGetVendors) {
            foreach (ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
                if (isset(self::$installedByVendor[$vendorDir])) {
                    $installed[] = self::$installedByVendor[$vendorDir];
                } elseif (\is_file($vendorDir . '/composer/installed.php')) {
                    $installed[] = self::$installedByVendor[$vendorDir] = (require $vendorDir . '/composer/installed.php');
                }
            }
        }
        $installed[] = self::$installed;
        return $installed;
    }
}

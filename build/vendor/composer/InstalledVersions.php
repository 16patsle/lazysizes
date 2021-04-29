<?php

namespace Lazysizes\Vendor\Composer;

use Lazysizes\Vendor\Composer\Autoload\ClassLoader;
use Lazysizes\Vendor\Composer\Semver\VersionParser;
class InstalledVersions
{
    private static $installed = array('root' => array('pretty_version' => 'dev-develop', 'version' => 'dev-develop', 'aliases' => array(), 'reference' => '757e7d8c9a30d381eb02531eecc58668dce4611c', 'name' => '16patsle/lazysizes'), 'versions' => array('16patsle/lazysizes' => array('pretty_version' => 'dev-develop', 'version' => 'dev-develop', 'aliases' => array(), 'reference' => '757e7d8c9a30d381eb02531eecc58668dce4611c'), 'composer/installers' => array('pretty_version' => 'v1.9.0', 'version' => '1.9.0.0', 'aliases' => array(), 'reference' => 'b93bcf0fa1fccb0b7d176b0967d969691cd74cca'), 'composer/package-versions-deprecated' => array('pretty_version' => '1.11.99.1', 'version' => '1.11.99.1', 'aliases' => array(), 'reference' => '7413f0b55a051e89485c5cb9f765fe24bb02a7b6'), 'dealerdirect/phpcodesniffer-composer-installer' => array('pretty_version' => 'v0.7.0', 'version' => '0.7.0.0', 'aliases' => array(), 'reference' => 'e8d808670b8f882188368faaf1144448c169c0b7'), 'humbug/php-scoper' => array('pretty_version' => '0.13.10', 'version' => '0.13.10.0', 'aliases' => array(), 'reference' => 'e56b0d91b344181c9a73119454c05a5498bf56e4', 'replaced' => array(0 => '0.13.10')), 'jetbrains/phpstorm-stubs' => array('pretty_version' => 'dev-master', 'version' => 'dev-master', 'aliases' => array(), 'reference' => '1b7fe33233bd0f29c2ebf3beccf622af8261e78a'), 'kornrunner/blurhash' => array('pretty_version' => 'v1.1.0', 'version' => '1.1.0.0', 'aliases' => array(), 'reference' => '5a09325353229c108c8d2ff129ec08b447753f9b'), 'nikic/php-parser' => array('pretty_version' => 'v4.6.0', 'version' => '4.6.0.0', 'aliases' => array(), 'reference' => 'c346bbfafe2ff60680258b631afb730d186ed864'), 'ocramius/package-versions' => array('replaced' => array(0 => '1.11.99')), 'phpcompatibility/php-compatibility' => array('pretty_version' => '9.3.5', 'version' => '9.3.5.0', 'aliases' => array(), 'reference' => '9fb324479acf6f39452e0655d2429cc0d3914243'), 'phpcompatibility/phpcompatibility-paragonie' => array('pretty_version' => '1.3.0', 'version' => '1.3.0.0', 'aliases' => array(), 'reference' => 'b862bc32f7e860d0b164b199bd995e690b4b191c'), 'phpcompatibility/phpcompatibility-wp' => array('pretty_version' => '2.1.0', 'version' => '2.1.0.0', 'aliases' => array(), 'reference' => '41bef18ba688af638b7310666db28e1ea9158b2f'), 'psr/container' => array('pretty_version' => '1.0.0', 'version' => '1.0.0.0', 'aliases' => array(), 'reference' => 'b7ce3b176482dbbc1245ebf52b181af44c2cf55f'), 'psr/log-implementation' => array('provided' => array(0 => '1.0')), 'roundcube/plugin-installer' => array('replaced' => array(0 => '*')), 'shama/baton' => array('replaced' => array(0 => '*')), 'squizlabs/php_codesniffer' => array('pretty_version' => '3.5.5', 'version' => '3.5.5.0', 'aliases' => array(), 'reference' => '73e2e7f57d958e7228fce50dc0c61f58f017f9f6'), 'symfony/console' => array('pretty_version' => 'v4.4.10', 'version' => '4.4.10.0', 'aliases' => array(), 'reference' => '326b064d804043005526f5a0494cfb49edb59bb0'), 'symfony/filesystem' => array('pretty_version' => 'v4.4.10', 'version' => '4.4.10.0', 'aliases' => array(), 'reference' => 'b27f491309db5757816db672b256ea2e03677d30'), 'symfony/finder' => array('pretty_version' => 'v4.4.10', 'version' => '4.4.10.0', 'aliases' => array(), 'reference' => '5729f943f9854c5781984ed4907bbb817735776b'), 'symfony/polyfill-ctype' => array('pretty_version' => 'v1.17.1', 'version' => '1.17.1.0', 'aliases' => array(), 'reference' => '2edd75b8b35d62fd3eeabba73b26b8f1f60ce13d'), 'symfony/polyfill-mbstring' => array('pretty_version' => 'v1.17.1', 'version' => '1.17.1.0', 'aliases' => array(), 'reference' => '7110338d81ce1cbc3e273136e4574663627037a7'), 'symfony/polyfill-php73' => array('pretty_version' => 'v1.17.1', 'version' => '1.17.1.0', 'aliases' => array(), 'reference' => 'fa0837fe02d617d31fbb25f990655861bb27bd1a'), 'symfony/polyfill-php80' => array('pretty_version' => 'v1.17.1', 'version' => '1.17.1.0', 'aliases' => array(), 'reference' => '4a5b6bba3259902e386eb80dd1956181ee90b5b2'), 'symfony/service-contracts' => array('pretty_version' => 'v2.1.3', 'version' => '2.1.3.0', 'aliases' => array(), 'reference' => '58c7475e5457c5492c26cc740cc0ad7464be9442'), 'wp-coding-standards/wpcs' => array('pretty_version' => '2.3.0', 'version' => '2.3.0.0', 'aliases' => array(), 'reference' => '7da1894633f168fe244afc6de00d141f27517b62')));
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
    public static function satisfies(\Lazysizes\Vendor\Composer\Semver\VersionParser $parser, $packageName, $constraint)
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
            self::$canGetVendors = \method_exists('Lazysizes\\Vendor\\Composer\\Autoload\\ClassLoader', 'getRegisteredLoaders');
        }
        $installed = array();
        if (self::$canGetVendors) {
            foreach (\Lazysizes\Vendor\Composer\Autoload\ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
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

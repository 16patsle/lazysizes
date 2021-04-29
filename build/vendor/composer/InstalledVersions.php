<?php

namespace Lazysizes\Vendor\Composer;

use Lazysizes\Vendor\Composer\Autoload\ClassLoader;
use Lazysizes\Vendor\Composer\Semver\VersionParser;
class InstalledVersions
{
    private static $installed = array('root' => array('pretty_version' => 'dev-develop', 'version' => 'dev-develop', 'aliases' => array(), 'reference' => 'b82765ae61f314e90357b56b18f90d2e19570cf5', 'name' => '16patsle/lazysizes'), 'versions' => array('16patsle/lazysizes' => array('pretty_version' => 'dev-develop', 'version' => 'dev-develop', 'aliases' => array(), 'reference' => 'b82765ae61f314e90357b56b18f90d2e19570cf5'), 'composer/installers' => array('pretty_version' => 'v1.11.0', 'version' => '1.11.0.0', 'aliases' => array(), 'reference' => 'ae03311f45dfe194412081526be2e003960df74b'), 'composer/package-versions-deprecated' => array('pretty_version' => '1.11.99.1', 'version' => '1.11.99.1', 'aliases' => array(), 'reference' => '7413f0b55a051e89485c5cb9f765fe24bb02a7b6'), 'dealerdirect/phpcodesniffer-composer-installer' => array('pretty_version' => 'v0.7.1', 'version' => '0.7.1.0', 'aliases' => array(), 'reference' => 'fe390591e0241955f22eb9ba327d137e501c771c'), 'doctrine/instantiator' => array('pretty_version' => '1.4.0', 'version' => '1.4.0.0', 'aliases' => array(), 'reference' => 'd56bf6102915de5702778fe20f2de3b2fe570b5b'), 'humbug/php-scoper' => array('pretty_version' => '0.13.10', 'version' => '0.13.10.0', 'aliases' => array(), 'reference' => 'e56b0d91b344181c9a73119454c05a5498bf56e4', 'replaced' => array(0 => '0.13.10')), 'jetbrains/phpstorm-stubs' => array('pretty_version' => 'dev-master', 'version' => 'dev-master', 'aliases' => array(0 => '9999999-dev'), 'reference' => '9533320237b26c81b9376fb1e012e739a118b689'), 'kornrunner/blurhash' => array('pretty_version' => 'v1.2.1', 'version' => '1.2.1.0', 'aliases' => array(), 'reference' => '7dde9c0c7ee7e688dc79fb7770ee110ed308ffc7'), 'myclabs/deep-copy' => array('pretty_version' => '1.10.2', 'version' => '1.10.2.0', 'aliases' => array(), 'reference' => '776f831124e9c62e1a2c601ecc52e776d8bb7220', 'replaced' => array(0 => '1.10.2')), 'nikic/php-parser' => array('pretty_version' => 'v4.10.4', 'version' => '4.10.4.0', 'aliases' => array(), 'reference' => 'c6d052fc58cb876152f89f532b95a8d7907e7f0e'), 'ocramius/package-versions' => array('replaced' => array(0 => '1.11.99')), 'phar-io/manifest' => array('pretty_version' => '1.0.3', 'version' => '1.0.3.0', 'aliases' => array(), 'reference' => '7761fcacf03b4d4f16e7ccb606d4879ca431fcf4'), 'phar-io/version' => array('pretty_version' => '2.0.1', 'version' => '2.0.1.0', 'aliases' => array(), 'reference' => '45a2ec53a73c70ce41d55cedef9063630abaf1b6'), 'phpcompatibility/php-compatibility' => array('pretty_version' => '9.3.5', 'version' => '9.3.5.0', 'aliases' => array(), 'reference' => '9fb324479acf6f39452e0655d2429cc0d3914243'), 'phpcompatibility/phpcompatibility-paragonie' => array('pretty_version' => '1.3.1', 'version' => '1.3.1.0', 'aliases' => array(), 'reference' => 'ddabec839cc003651f2ce695c938686d1086cf43'), 'phpcompatibility/phpcompatibility-wp' => array('pretty_version' => '2.1.1', 'version' => '2.1.1.0', 'aliases' => array(), 'reference' => 'b7dc0cd7a8f767ccac5e7637550ea1c50a67b09e'), 'phpdocumentor/reflection-common' => array('pretty_version' => '2.2.0', 'version' => '2.2.0.0', 'aliases' => array(), 'reference' => '1d01c49d4ed62f25aa84a747ad35d5a16924662b'), 'phpdocumentor/reflection-docblock' => array('pretty_version' => '5.2.2', 'version' => '5.2.2.0', 'aliases' => array(), 'reference' => '069a785b2141f5bcf49f3e353548dc1cce6df556'), 'phpdocumentor/type-resolver' => array('pretty_version' => '1.4.0', 'version' => '1.4.0.0', 'aliases' => array(), 'reference' => '6a467b8989322d92aa1c8bf2bebcc6e5c2ba55c0'), 'phpspec/prophecy' => array('pretty_version' => '1.13.0', 'version' => '1.13.0.0', 'aliases' => array(), 'reference' => 'be1996ed8adc35c3fd795488a653f4b518be70ea'), 'phpunit/php-code-coverage' => array('pretty_version' => '6.1.4', 'version' => '6.1.4.0', 'aliases' => array(), 'reference' => '807e6013b00af69b6c5d9ceb4282d0393dbb9d8d'), 'phpunit/php-file-iterator' => array('pretty_version' => '2.0.3', 'version' => '2.0.3.0', 'aliases' => array(), 'reference' => '4b49fb70f067272b659ef0174ff9ca40fdaa6357'), 'phpunit/php-text-template' => array('pretty_version' => '1.2.1', 'version' => '1.2.1.0', 'aliases' => array(), 'reference' => '31f8b717e51d9a2afca6c9f046f5d69fc27c8686'), 'phpunit/php-timer' => array('pretty_version' => '2.1.3', 'version' => '2.1.3.0', 'aliases' => array(), 'reference' => '2454ae1765516d20c4ffe103d85a58a9a3bd5662'), 'phpunit/php-token-stream' => array('pretty_version' => '3.1.2', 'version' => '3.1.2.0', 'aliases' => array(), 'reference' => '472b687829041c24b25f475e14c2f38a09edf1c2'), 'phpunit/phpunit' => array('pretty_version' => '7.5.20', 'version' => '7.5.20.0', 'aliases' => array(), 'reference' => '9467db479d1b0487c99733bb1e7944d32deded2c'), 'psr/container' => array('pretty_version' => '1.1.1', 'version' => '1.1.1.0', 'aliases' => array(), 'reference' => '8622567409010282b7aeebe4bb841fe98b58dcaf'), 'psr/log-implementation' => array('provided' => array(0 => '1.0')), 'roundcube/plugin-installer' => array('replaced' => array(0 => '*')), 'sebastian/code-unit-reverse-lookup' => array('pretty_version' => '1.0.2', 'version' => '1.0.2.0', 'aliases' => array(), 'reference' => '1de8cd5c010cb153fcd68b8d0f64606f523f7619'), 'sebastian/comparator' => array('pretty_version' => '3.0.3', 'version' => '3.0.3.0', 'aliases' => array(), 'reference' => '1071dfcef776a57013124ff35e1fc41ccd294758'), 'sebastian/diff' => array('pretty_version' => '3.0.3', 'version' => '3.0.3.0', 'aliases' => array(), 'reference' => '14f72dd46eaf2f2293cbe79c93cc0bc43161a211'), 'sebastian/environment' => array('pretty_version' => '4.2.4', 'version' => '4.2.4.0', 'aliases' => array(), 'reference' => 'd47bbbad83711771f167c72d4e3f25f7fcc1f8b0'), 'sebastian/exporter' => array('pretty_version' => '3.1.3', 'version' => '3.1.3.0', 'aliases' => array(), 'reference' => '6b853149eab67d4da22291d36f5b0631c0fd856e'), 'sebastian/global-state' => array('pretty_version' => '2.0.0', 'version' => '2.0.0.0', 'aliases' => array(), 'reference' => 'e8ba02eed7bbbb9e59e43dedd3dddeff4a56b0c4'), 'sebastian/object-enumerator' => array('pretty_version' => '3.0.4', 'version' => '3.0.4.0', 'aliases' => array(), 'reference' => 'e67f6d32ebd0c749cf9d1dbd9f226c727043cdf2'), 'sebastian/object-reflector' => array('pretty_version' => '1.1.2', 'version' => '1.1.2.0', 'aliases' => array(), 'reference' => '9b8772b9cbd456ab45d4a598d2dd1a1bced6363d'), 'sebastian/recursion-context' => array('pretty_version' => '3.0.1', 'version' => '3.0.1.0', 'aliases' => array(), 'reference' => '367dcba38d6e1977be014dc4b22f47a484dac7fb'), 'sebastian/resource-operations' => array('pretty_version' => '2.0.2', 'version' => '2.0.2.0', 'aliases' => array(), 'reference' => '31d35ca87926450c44eae7e2611d45a7a65ea8b3'), 'sebastian/version' => array('pretty_version' => '2.0.1', 'version' => '2.0.1.0', 'aliases' => array(), 'reference' => '99732be0ddb3361e16ad77b68ba41efc8e979019'), 'shama/baton' => array('replaced' => array(0 => '*')), 'squizlabs/php_codesniffer' => array('pretty_version' => '3.6.0', 'version' => '3.6.0.0', 'aliases' => array(), 'reference' => 'ffced0d2c8fa8e6cdc4d695a743271fab6c38625'), 'symfony/console' => array('pretty_version' => 'v4.4.21', 'version' => '4.4.21.0', 'aliases' => array(), 'reference' => '1ba4560dbbb9fcf5ae28b61f71f49c678086cf23'), 'symfony/filesystem' => array('pretty_version' => 'v4.4.21', 'version' => '4.4.21.0', 'aliases' => array(), 'reference' => '940826c465be2690c9fae91b2793481e5cbd6834'), 'symfony/finder' => array('pretty_version' => 'v4.4.20', 'version' => '4.4.20.0', 'aliases' => array(), 'reference' => '2543795ab1570df588b9bbd31e1a2bd7037b94f6'), 'symfony/polyfill-ctype' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => 'c6c942b1ac76c82448322025e084cadc56048b4e'), 'symfony/polyfill-mbstring' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => '5232de97ee3b75b0360528dae24e73db49566ab1'), 'symfony/polyfill-php73' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => 'a678b42e92f86eca04b7fa4c0f6f19d097fb69e2'), 'symfony/polyfill-php80' => array('pretty_version' => 'v1.22.1', 'version' => '1.22.1.0', 'aliases' => array(), 'reference' => 'dc3063ba22c2a1fd2f45ed856374d79114998f91'), 'symfony/service-contracts' => array('pretty_version' => 'v2.4.0', 'version' => '2.4.0.0', 'aliases' => array(), 'reference' => 'f040a30e04b57fbcc9c6cbcf4dbaa96bd318b9bb'), 'theseer/tokenizer' => array('pretty_version' => '1.2.0', 'version' => '1.2.0.0', 'aliases' => array(), 'reference' => '75a63c33a8577608444246075ea0af0d052e452a'), 'webmozart/assert' => array('pretty_version' => '1.10.0', 'version' => '1.10.0.0', 'aliases' => array(), 'reference' => '6964c76c7804814a842473e0c8fd15bab0f18e25'), 'wp-coding-standards/wpcs' => array('pretty_version' => '2.3.0', 'version' => '2.3.0.0', 'aliases' => array(), 'reference' => '7da1894633f168fe244afc6de00d141f27517b62')));
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

<?php

namespace Bolt\Site\Installer;

use Silex;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Yaml;

class Application extends Silex\Application
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this['yaml_parser'] = function () {
            return new Yaml\Parser();
        };

        $this['config_cache_factory'] = function ($container) {
            return new ConfigCacheFactory($container['debug']);
        };

        $this->register(new Silex\Provider\TwigServiceProvider(), [
            'twig.path'       => __DIR__ . '/../view/templates',
            'twig.options'    => [
                'cache' => __DIR__ . '/../var/cache/twig',
            ],
        ]);
        $this->register(new Silex\Provider\ServiceControllerServiceProvider());
        $this->register(new Silex\Provider\HttpFragmentServiceProvider());
        $this->register(new Silex\Provider\VarDumperServiceProvider());
        $this->register(new Silex\Provider\AssetServiceProvider());
        $this->register(new Silex\Provider\MonologServiceProvider());
        $this->register(new Silex\Provider\FormServiceProvider());
        $this->register(new Silex\Provider\DoctrineServiceProvider());
        $this->register(new Silex\Provider\HttpCacheServiceProvider());
        $this->register(new Provider\ConsoleServiceProvider());

        $config = [];
        $configFile = __DIR__ . '/../app/config/config.yml';
        if (file_exists($configFile)) {
            $config = Yaml\Yaml::parse(file_get_contents(__DIR__ . '/../app/config/config.yml'));
        }
        $this['debug'] = isset($config['debug']) ? $config['debug'] : true;

        if ($this['debug']) {
            $this->register(new Silex\Provider\WebProfilerServiceProvider(), [
                'profiler.cache_dir' => __DIR__ . '/../var/cache/profiler',
            ]);
        }
        ini_set('display_errors', (int) $this['debug']);

        $this['monolog.logfile'] = __DIR__ . '/../var/log/system.log';

        $app['assets.base_path'] = __DIR__ . '/../web';
    }

    public function flush()
    {
        $this->mount('', new Controllers());

        parent::flush();
    }
}

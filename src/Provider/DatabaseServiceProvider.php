<?php

namespace Bolt\Site\Installer\Provider;

use Dflydev;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Tools\Setup;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Ramsey\Uuid\Doctrine\UuidType;
use Silex;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DatabaseServiceProvider
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->register(new Silex\Provider\DoctrineServiceProvider());

        $container['db.options'] = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/../app/database/site.db',
        ];

        Type::addType('uuid', UuidType::class);

        $container->register(new Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider([
            'orm.proxies_dir' => __DIR__ . '/../var/cache/doctrine/proxy',
            'orm.em.options' => [
                'mappings' => [
                    [
                        'type' => 'yml',
                        'namespace' => 'Bolt\Site\Installer\Entity',
                        'path' => realpath(__DIR__ . '/../Resources/mappings/'),
                    ],
                ],
            ],
        ]));

        $container['orm.proxies_dir'] = function () {
            $fs = new Filesystem();
            $path = __DIR__ . '/../../var/cache/doctrine/proxy';
            if (!$fs->exists($path)) {
                $fs->mkdir($path);
            }

            return realpath($path);
        };

        $container['orm.em.config'] = function () {
            return Setup::createYAMLMetadataConfiguration(
                [
                    realpath(__DIR__ . '/../Resources/mappings')
                ],
                true,
                __DIR__ . '/../var/cache/doctrine/proxy'
            );
        };
    }
}

<?php

namespace Bolt\Site\Installer\Provider;

use Bolt\Site\Installer\Command;
use Doctrine\DBAL;
use Doctrine\ORM;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console;

class ConsoleServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['console'] = function ($container) {
            $console = new Console\Application('Get Bolt Site', '');

            $console->addCommands($container['console.commands']);

            $helpers = $console->getHelperSet();
            $ormHelpers = ORM\Tools\Console\ConsoleRunner::createHelperSet($container['orm.em']);
            foreach ($ormHelpers->getIterator() as $alias => $helper) {
                $helpers->set($helper, $alias);
            }
            $console->setHelperSet($helpers);

            return $console;
        };

        $container['console.commands'] = function ($container) {
            return [
                new Command\DumpVersions(),

                // DBAL Commands
                new DBAL\Tools\Console\Command\RunSqlCommand(),
                new DBAL\Tools\Console\Command\ImportCommand(),

                // ORM Commands
                new ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
                new ORM\Tools\Console\Command\ClearCache\ResultCommand(),
                new ORM\Tools\Console\Command\ClearCache\QueryCommand(),
                new ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
                new ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
                new ORM\Tools\Console\Command\SchemaTool\DropCommand(),
                new ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
                new ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
                new ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
                new ORM\Tools\Console\Command\GenerateEntitiesCommand(),
                new ORM\Tools\Console\Command\GenerateProxiesCommand(),
                new ORM\Tools\Console\Command\ConvertMappingCommand(),
                new ORM\Tools\Console\Command\RunDqlCommand(),
                new ORM\Tools\Console\Command\ValidateSchemaCommand(),
                new ORM\Tools\Console\Command\InfoCommand(),
                new ORM\Tools\Console\Command\MappingDescribeCommand(),
            ];
        };
    }
}

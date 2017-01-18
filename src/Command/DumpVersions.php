<?php

namespace Bolt\Site\Installer\Command;

use Bolt\Site\Installer\Git\VersionDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DumpVersions
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DumpVersions extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dump:versions')
            ->setDescription('Dump public/versions.json.')
            ->addOption('repo', null, InputOption::VALUE_REQUIRED, 'Path to the clone of bolt/bolt repository.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $repoPath = $input->getOption('repo');
        if (!$fs->exists($repoPath)) {
            throw new \RuntimeException(sprintf('The repository directory %s does not exist', $repoPath));
        }

        $output->writeln("<info>\n Dumping versions from git …</info>");
        $versions = VersionDumper::dump($repoPath, __DIR__ . '/../../web/versions.json');

        $table = new Table($output);
        $table
            ->setHeaders(['Release', 'Versions'])
        ;
        foreach ($versions as $majorVersion => $minorVersions) {
            foreach ($minorVersions as $minorVersion => $patchVersions) {
                $table->addRow([$minorVersion, implode(', ', $patchVersions)]);
            }
        }

        $output->writeln("<info>\n Dumped the following versions:\n</info>");
        $table->render();
    }
}

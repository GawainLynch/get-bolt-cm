<?php

namespace Bolt\Site\Installer\Git;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Dumper for Bolt versions in git.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class VersionDumper
{
    /**
     * @param string $repoDir
     * @param string $targetFile
     *
     * @throws RuntimeException
     * @throws ProcessFailedException
     *
     * @return array
     */
    public static function dump($repoDir, $targetFile)
    {
        $fs = new Filesystem();
        if (!$fs->exists($repoDir)) {
            throw new RuntimeException(sprintf('Directory does not exist: %s', $repoDir));
        }

        $process = new Process(sprintf('cd %s; git tag | grep -E "^v\d*" | sed "s/^v//g"', $repoDir));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $results = $process->getOutput();
        $results = explode("\n", trim($results));

        $arr = [];
        foreach ($results as $result) {
            // Only get stable SemVer entries
            if (!preg_match('#^\d+\.\d+\.\d+$#', $result)) {
                continue;
            }
            $parts = explode('.', $result);
            $verMajor = sprintf('%s.x', $parts[0]);
            $verMajorMinor = sprintf('%s.%s', $parts[0], $parts[1]);

            $arr[$verMajor][$verMajorMinor][]  = $result;
        }

        // Sort major keys
        krsort($arr, SORT_NUMERIC);
        array_walk($arr, function (&$a) {
            // Sort minor keys
            krsort($a, SORT_NATURAL);

            // Sort SemVer values
            array_walk($a, function (&$v) {
                rsort($v, SORT_NATURAL);
            });
        });

        $fs->dumpFile($targetFile, json_encode($arr, JSON_PRETTY_PRINT));

        return $arr;
    }
}

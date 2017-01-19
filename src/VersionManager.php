<?php

namespace Bolt\Site\Installer;

/**
 * Version manager.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class VersionManager
{
    /** @var array */
    protected $versions;
    /** @var string */
    protected $latest;
    /** @var boolean */
    protected $loaded;

    /**
     * @return array
     */
    public function getVersions()
    {
        $this->loadVersionsJson();

        return $this->versions;
    }

    /**
     * @return string
     */
    public function getLatest()
    {
        $this->loadVersionsJson();

        return $this->latest;
    }

    /**
     * Load JSON file.
     */
    protected function loadVersionsJson()
    {
        if ($this->versions !== null) {
            return;
        }

        $jsonFile = realpath(__DIR__ . '/../web/versions.json');
        if ($jsonFile === false) {
            return;
        }

        try {
            $this->versions = \GuzzleHttp\json_decode(file_get_contents(__DIR__ . '/../web/versions.json'), true);
            $latest = reset($this->versions);
            $latest = reset($latest);
            $this->latest = reset($latest);
        } catch (\InvalidArgumentException $e) {
            $this->latest = 'unknown';
        }
    }
}

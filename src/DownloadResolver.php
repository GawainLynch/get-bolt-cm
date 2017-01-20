<?php

namespace Bolt\Site\Installer;

use Bolt\Site\Installer\Exception\InvalidVersionException;
use Bolt\Site\Installer\Exception\MissingVersionsJsonException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Download URL resolver.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DownloadResolver
{
    /** @var string|null */
    protected $majorMinor;
    /** @var string|null */
    protected $majorMinorPatch;
    /** @var string|null */
    protected $phpVersion;
    /** @var bool */
    protected $flat;

    /**
     * Create new chainable instance.
     *
     * @return DownloadResolver
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Return a valid download URLK.
     *
     * @return string
     */
    public function getUrl()
    {
        $this->assertVersions();

        $majorMinor = $this->getMajorMinor();
        $majorMinorPatch = $this->getMajorMinorPatch();
        $suffix = $this->flat ? '-flat-structure' : '';

        $url = sprintf(
            'https://bolt.cm/distribution/archive/%s/bolt-%s%s.tar.gz',
            $majorMinor,
            $majorMinorPatch,
            $suffix
        );

        return $url;
    }

    /**
     * Assert versions are all valid.
     *
     * @throws InvalidVersionException
     */
    private function assertVersions()
    {
        $versions = $this->loadVersions();
        $major = $this->getMajor();
        $majorMinor = $this->getMajorMinor();
        $majorMinorPatch = $this->getMajorMinorPatch();

        if (!isset($versions[$major])) {
            throw new InvalidVersionException(sprintf(
                "Requested major version (%s) does not exists in catalogue.\n" .
                "Available: %s",
                $major,
                implode(', ', $versions[$major])
            ));
        }
        if (!isset($versions[$major][$majorMinor])) {
            throw new InvalidVersionException(sprintf(
                "Requested X.Y (%s) does not exists in catalogue.\n" .
                "Available: %s",
                $majorMinor,
                implode(', ', $versions[$major][$majorMinor])
            ));
        }
        $patches = array_flip($versions[$major][$majorMinor]);
        if (!isset($patches[$majorMinorPatch])) {
            throw new InvalidVersionException(sprintf(
                "Requested X.Y.Z (%s) does not exists in catalogue\n" .
                "Available:\n - %s",
                $majorMinorPatch,
                implode(', ', $versions[$major][$majorMinor])
            ));
        }
    }

    /**
     * @return string
     */
    public function getMajor()
    {
        Validator::assertMajorMinor($this->majorMinor);
        $parts = explode('.', $this->majorMinor);

        return reset($parts) . '.x';
    }

    /**
     * @return string
     */
    public function getMajorMinor()
    {
        Validator::assertMajorMinor($this->majorMinor);

        return $this->majorMinor;
    }

    /**
     * @param string $majorMinor
     *
     * @return DownloadResolver
     */
    public function setMajorMinor($majorMinor)
    {
        Validator::assertMajorMinor($majorMinor);
        $this->majorMinor = $majorMinor;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMajorMinorPatch()
    {
        Validator::assertMajorMinorPatch($this->majorMinorPatch);

        return $this->majorMinorPatch;
    }

    /**
     * @param null|string $majorMinorPatch
     *
     * @return DownloadResolver
     */
    public function setMajorMinorPatch($majorMinorPatch)
    {
        Validator::assertMajorMinorPatch($majorMinorPatch);
        $this->majorMinorPatch = $majorMinorPatch;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPhpVersion()
    {
        return $this->phpVersion;
    }

    /**
     * @param null|string $phpVersion
     *
     * @return DownloadResolver
     */
    public function setPhpVersion($phpVersion)
    {
        $this->phpVersion = $phpVersion;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFlat()
    {
        return (bool) $this->flat;
    }

    /**
     * @param bool $flat
     *
     * @return DownloadResolver
     */
    public function setFlat($flat)
    {
        $this->flat = $flat;

        return $this;
    }

    /**
     * @throws MissingVersionsJsonException
     *
     * @return array
     */
    private function loadVersions()
    {
        $fs = new Filesystem();
        $jsonFile = realpath(__DIR__ . '/../web/versions.json');
        if (!$fs->exists($jsonFile)) {
            throw new MissingVersionsJsonException(sprintf('File not found: %s', $jsonFile));
        }
        $json = file_get_contents($jsonFile);

        return \GuzzleHttp\json_decode($json, true);
    }
}

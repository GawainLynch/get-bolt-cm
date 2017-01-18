<?php

namespace Bolt\Site\Installer\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Download entity.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Download
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $version;
    /** @var string */
    protected $phpVersion;
    /** @var \DateTime */
    protected $date;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     *
     * @return Download
     */
    public function setVersion(string $version): Download
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    /**
     * @param string $phpVersion
     *
     * @return Download
     */
    public function setPhpVersion(string $phpVersion): Download
    {
        $this->phpVersion = $phpVersion;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return Download
     */
    public function setDate(\DateTime $date): Download
    {
        $this->date = $date;

        return $this;
    }
}

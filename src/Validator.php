<?php

namespace Bolt\Site\Installer;

use Bolt\Site\Installer\Exception\InvalidVersionException;

/**
 * Validator for version strings to work with.
 *
 * @TODO move to Symfony Validator.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Validator
{
    const REGEX_MAJOR_MINOR = '^(\d+\.(\d+|x))$';
    const REGEX_MAJOR_MINOR_PATCH = '^(\d+\.\d+\.\d+(\-(beta|dev|pl|rc)\d+)?)$';

    /**
     * Check a string is valid "X.Y" format.
     *
     * @param string $majorMinor
     *
     * @return bool
     */
    public static function isMajorMinor($majorMinor)
    {
        return (bool) preg_match('/' . static::REGEX_MAJOR_MINOR . '/', $majorMinor);
    }

    /**
     * Check a string is a valid "X.Y.Z" format, with optional
     * "-(beta|dev|pl|rc)N" suffix.
     *
     * @param string $majorMinorPatch
     *
     * @return bool
     */
    public static function isMajorMinorPatch($majorMinorPatch)
    {
        return (bool) preg_match('/' . static::REGEX_MAJOR_MINOR_PATCH . '/', $majorMinorPatch);
    }

    /**
     * Assert a string is a valid "X.Y" format.
     *
     * @param string $majorMinor
     *
     * @throws InvalidVersionException
     */
    public static function assertMajorMinor($majorMinor)
    {
        if (!static::isMajorMinor($majorMinor)) {
            throw new InvalidVersionException(sprintf('"%s" is not a valid "X.Y" format', $majorMinor));
        }
    }

    /**
     * Assert a string is a valid "X.Y.Z" format, with optional
     * "-(beta|dev|pl|rc)N" suffix.
     *
     * @param string $majorMinorPatch
     *
     * @throws InvalidVersionException
     */
    public static function assertMajorMinorPatch($majorMinorPatch)
    {
        if (!static::isMajorMinorPatch($majorMinorPatch)) {
            throw new InvalidVersionException(sprintf('"%s" is not a valid "X.Y.Z" or "X.Y.Z-betaN" format', $majorMinorPatch));
        }
    }
}

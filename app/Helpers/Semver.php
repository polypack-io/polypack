<?php

namespace App\Helpers;

class Semver
{
    public string $version;

    public int $major;

    public int $minor;

    public int $patch;

    public ?string $prerelease;

    public ?string $build;

    private static string $regex = "#^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$#";

    public function __construct(string $version)
    {
        $this->version = $version;

        if (! self::isValid($version)) {
            throw new \InvalidArgumentException('Invalid version');
        }

        $capture = [];
        preg_match(self::$regex, $version, $capture);

        $this->major = $capture[1];
        $this->minor = $capture[2];
        $this->patch = $capture[3];
        $this->prerelease = $capture[4] ?? null;
        $this->build = $capture[5] ?? null;
    }

    public static function isValid(string $version): bool
    {
        return preg_match(self::$regex, $version) === 1;
    }
}

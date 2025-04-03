<?php

namespace App\Jobs\Package;

use App\Actions\Package\UpdatedVersions;
use App\Helpers\Semver;
use App\Models\Package;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateVersion implements ShouldQueue
{
    use Queueable;

    public Package $package;

    public array $data;

    public string $version;

    public bool $enforceSemver;

    /**
     * Create a new job instance.
     */
    public function __construct(Package $package, array $data, string $version, bool $enforceSemver = true)
    {
        $this->package = $package;
        $this->data = $data;
        $this->version = $version;
        $this->enforceSemver = $enforceSemver;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $isDev = true;

        try {
            $semver = new Semver($this->version);

            if (! isset($semver->prerelease)) {
                $isDev = false;
            }
        } catch (\Exception $e) {
            if ($this->enforceSemver) {
                return;
            }
        }

        $this->package->packageProvider()->createVersion(
            $this->package,
            $this->package->repositoryProvider->getService(),
            $this->data,
            $this->version,
            $isDev,
            $this->package->versions_are_private_by_default
        );

        app(UpdatedVersions::class)->updatedVersions($this->package);
    }
}

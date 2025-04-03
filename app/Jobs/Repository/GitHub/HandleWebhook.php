<?php

namespace App\Jobs\Repository\GitHub;

use App\Jobs\Package\CreateVersion;
use App\Models\Log as ModelsLog;
use App\Models\Package;
use App\Models\PackageVersion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HandleWebhook implements ShouldQueue
{
    use Queueable;

    public array $request;

    public Package $package;

    /**
     * Create a new job instance.
     */
    public function __construct(array $request, Package $package)
    {
        $this->request = $request;
        $this->package = $package;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info($this->request);

        if (! isset($this->request['ref'])) {
            return;
        }

        $ref = $this->request['ref'];

        if (Str::startsWith($ref, 'refs/tags/')) {
            $version = Str::after($ref, 'refs/tags/');

            if ($this->request['deleted']) {
                $version = Str::after($ref, 'refs/tags/');

                PackageVersion::where('package_id', $this->package->id)->where('version', $version)->delete();
            }

            CreateVersion::dispatch($this->package, [
                'ref' => $this->request['after'],
            ], $version);
        }

        if (Str::startsWith($ref, 'refs/heads/')) {
            $version = Str::after($ref, 'refs/heads/');

            if ($this->request['deleted']) {
                PackageVersion::where('package_id', $this->package->id)->where('version', 'dev-'.$version)->delete();
            }

            CreateVersion::dispatch($this->package, [
                'ref' => $this->request['after'],
            ], 'dev-'.$version, false);

            ModelsLog::write($this->package, 'package.version.created', 'Created version '.$version.' from webhook.');
        }
    }
}

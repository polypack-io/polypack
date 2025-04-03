<?php

namespace App\Jobs\Package;

use App\Models\Package;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class Initialize implements ShouldQueue
{
    use Queueable;

    public Package $package;

    /**
     * Create a new job instance.
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->package->packageProvider()->initializePackage($this->package);
    }
}

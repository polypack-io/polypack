<?php

namespace App\Actions\Package;

use App\Models\Package;

class UpdatedVersions
{
    public function updatedVersions(Package $package): void
    {
        $package->versions_updated_at = now();
        $package->save();
    }
}

<?php

namespace App\Http\APIControllers\Webhooks;

use App\Http\APIControllers\APIController;
use App\Jobs\Repository\GitHub\HandleWebhook;
use App\Models\Package;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Post;

class GitHubController extends APIController
{
    #[Post('/github/webhook/{package}', name: 'github.webhook')]
    public function webhook(Request $request, Package $package)
    {
        HandleWebhook::dispatch($request->all(), $package);
    }
}

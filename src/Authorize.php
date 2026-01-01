<?php

namespace Julienbourdeau\RouteUsage;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;

class Authorize
{
    public function handle($request, $next)
    {
        return $this->check($request) ? $next($request) : abort(403);
    }

    protected function check($request)
    {
        return Gate::check('viewRouteUsage', [$request->user()]);
    }
}

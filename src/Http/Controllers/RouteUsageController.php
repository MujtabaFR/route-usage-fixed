<?php

namespace Julienbourdeau\RouteUsage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Julienbourdeau\RouteUsage\RouteUsage;

class RouteUsageController extends Controller
{
    public function index(Request $request)
    {
        $order = $request->get('orderBy', 'updated_at');
        $sort  = $request->get('sort', 'desc');

        if (!in_array($sort, ['asc', 'desc'])) {
            $sort = 'desc';
        }

        /**
         * 1) Get all Laravel routes
         */
        $laravelRoutes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'method' => collect($route->methods())
                    ->reject(fn ($m) => $m === 'HEAD')
                    ->implode('|'),

                'path'   => $route->uri(),
                'action' => $route->getActionName(),
                'name'   => $route->getName(),
            ];
        });

        /**
         * 2) Get all usage records indexed by METHOD.PATH
         */
        $routeUsage = RouteUsage::all()
            ->keyBy(fn ($r) => $r->method . '.' . $r->path);

        /**
         * 3) Merge routes + usage (LEFT JOIN behavior)
         */
        $routes = $laravelRoutes->map(function ($route) use ($routeUsage) {
            $key   = $route['method'] . '.' . $route['path'];
            $usage = $routeUsage->get($key);

            return (object) [
                'id'          => $usage->id ?? '-',
                'path'        => $route['path'],
                'method'      => $route['method'],
                'action'      => $route['action'],
                'status_code' => $usage?->status_code,
                'updated_at'  => $usage?->updated_at,
                'last_used'   => $usage
                    ? $usage->updated_at->diffForHumans()
                    : 'Never',
            ];
        });

        /**
         * 4) Sorting (supports DB + virtual fields)
         */
        if ($order === 'updated_at') {
            $routes = $routes->sortBy(
                fn ($r) => $r->updated_at ?? now()->subYears(50),
                SORT_REGULAR,
                $sort === 'desc'
            );
        } else {
            $routes = $routes->sortBy(
                fn ($r) => $r->{$order} ?? '',
                SORT_REGULAR,
                $sort === 'desc'
            );
        }

        return view('route-usage::index', [
            'routes' => $routes->values(),
        ]);
    }
}

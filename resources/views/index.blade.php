<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Route Usage</title>

    <!-- Tailwind v3 -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 text-slate-800 antialiased">

<div class="mx-8 my-10">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Route Usage</h1>
        <p class="text-sm text-slate-500 mt-1">
            All registered routes with last access time
        </p>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-auto max-h-[75vh]">
            <table id="routeTable" class="min-w-full text-sm">
                <thead class="sticky top-0 bg-slate-50 border-b border-slate-200">
                <tr class="text-left text-slate-600">
                    <th class="px-4 py-3 font-medium cursor-pointer sortable" data-key="id" data-type="number">
                        ID <span class="sort-indicator"></span>
                    </th>
                    <th class="px-4 py-3 font-medium cursor-pointer sortable" data-key="path" data-type="string">
                        Route <span class="sort-indicator"></span>
                    </th>
                    <th class="px-4 py-3 font-medium cursor-pointer sortable" data-key="method" data-type="string">
                        Method <span class="sort-indicator"></span>
                    </th>
                    <th class="px-4 py-3 font-medium cursor-pointer sortable" data-key="status" data-type="number">
                        Status <span class="sort-indicator"></span>
                    </th>
                    <th class="px-4 py-3 font-medium cursor-pointer sortable" data-key="lastUsed" data-type="date">
                        Last Used <span class="sort-indicator"></span>
                    </th>
                </tr>
                </thead>

                <tbody id="routeTableBody" class="divide-y divide-slate-100">
                @foreach($routes as $route)
                    <tr class="hover:bg-slate-50 transition"
                        data-id="{{ $route->id ?? 0 }}"
                        data-path="{{ $route->path }}"
                        data-method="{{ $route->method }}"
                        data-status="{{ $route->status_code ?? 0 }}"
                        data-last-used="{{ $route->updated_at ? $route->updated_at->timestamp : 0 }}"
                    >
                        <!-- ID -->
                        <td class="px-4 py-3 text-slate-500">
                            {{ $route->id ?? '—' }}
                        </td>

                        <!-- Route -->
                        <td class="px-4 py-3">
                            <div class="font-mono text-slate-900">
                                {{ $route->path }}
                            </div>
                            <div class="text-xs text-slate-500 truncate max-w-[700px]">
                                {{ $route->action }}
                            </div>
                        </td>

                        <!-- Method -->
                        <td class="px-4 py-3">
                            @foreach(explode('|', $route->method) as $method)
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium
                                    @class([
                                        'bg-green-100 text-green-700' => $method === 'GET',
                                        'bg-blue-100 text-blue-700' => $method === 'POST',
                                        'bg-yellow-100 text-yellow-700' => $method === 'PUT',
                                        'bg-red-100 text-red-700' => $method === 'DELETE',
                                        'bg-slate-100 text-slate-700' => !in_array($method, ['GET','POST','PUT','DELETE']),
                                    ])">
                                    {{ $method }}
                                </span>
                            @endforeach
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3">
                            @if($route->status_code)
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium
                                    {{ $route->status_code >= 500 ? 'bg-red-100 text-red-700' :
                                       ($route->status_code >= 400 ? 'bg-yellow-100 text-yellow-700' :
                                       'bg-green-100 text-green-700') }}">
                                    {{ $route->status_code }}
                                </span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- Last Used -->
                        <td class="px-4 py-3">
                            @if($route->updated_at)
                                {{ $route->updated_at->diffForHumans() }}
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-slate-200 text-slate-600">
                                    Never
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sorting Script -->
<script>
    const headers = document.querySelectorAll('.sortable');
    const tbody = document.getElementById('routeTableBody');

    let currentSort = { key: null, dir: 'asc' };

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const key = header.dataset.key;
            const type = header.dataset.type;

            currentSort.dir = (currentSort.key === key && currentSort.dir === 'asc') ? 'desc' : 'asc';
            currentSort.key = key;

            headers.forEach(h => h.querySelector('.sort-indicator').textContent = '');
            header.querySelector('.sort-indicator').textContent =
                currentSort.dir === 'asc' ? '▲' : '▼';

            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                let aVal = a.dataset[key] ?? '';
                let bVal = b.dataset[key] ?? '';

                if (type === 'number') {
                    return currentSort.dir === 'asc'
                        ? aVal - bVal
                        : bVal - aVal;
                }

                if (type === 'date') {
                    return currentSort.dir === 'asc'
                        ? aVal - bVal
                        : bVal - aVal;
                }

                return currentSort.dir === 'asc'
                    ? aVal.localeCompare(bVal)
                    : bVal.localeCompare(aVal);
            });

            rows.forEach(row => tbody.appendChild(row));
        });
    });
</script>

</body>
</html>

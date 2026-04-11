<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Health Check — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen font-sans p-6 lg:p-10">

<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-semibold mb-1">Health Check</h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm">
            {{ config('app.name') }} — {{ now()->format('d.m.Y H:i:s') }}
        </p>
        <div class="mt-3">
            @if ($allOk)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-sm font-medium">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    All systems operational
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-sm font-medium">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    Issues detected
                </span>
            @endif
        </div>
    </div>

    {{-- Service checks --}}
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden mb-8">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Services</h2>
        </div>

        <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach ($checks as $key => $check)
                <li id="check-{{ $key }}" class="px-5 py-3 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            @if ($check['ok'])
                                <span class="check-dot w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                            @else
                                <span class="check-dot w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                            @endif
                            <span class="font-medium text-sm">{{ $check['name'] }}</span>
                        </div>

                        @if (!empty($check['error']))
                            @if ($key === 'tests')
                                <pre class="check-details text-xs text-red-500 mt-1 ml-4 whitespace-pre-wrap break-all max-h-40 overflow-y-auto">{{ $check['error'] }}</pre>
                            @else
                                <p class="check-details text-xs text-red-500 mt-1 ml-4 break-all">{{ $check['error'] }}</p>
                            @endif
                        @endif

                        {{-- PHP Extensions detail --}}
                        @if ($key === 'php_extensions' && is_array($check['details'] ?? null))
                            <div class="flex flex-wrap gap-1.5 mt-1.5 ml-4">
                                @foreach ($check['details'] as $ext => $loaded)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs
                                        {{ $loaded
                                            ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400'
                                            : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400' }}">
                                        {{ $ext }}
                                        @if ($loaded) ✓ @else ✗ @endif
                                    </span>
                                @endforeach
                            </div>
                        @elseif ($key !== 'php_extensions' && !empty($check['details']))
                            <p class="check-details text-xs text-gray-500 dark:text-gray-400 mt-0.5 ml-4">{{ $check['details'] }}</p>
                        @endif
                    </div>

                    <div class="shrink-0 text-right">
                        @if (in_array($key, ['tests', 'migrations', 'schedule']))
                            <button type="button"
                                    data-check-type="{{ $key }}"
                                    class="health-check-btn text-xs text-blue-500 hover:underline cursor-pointer">
                                Check
                            </button>
                        @elseif (!empty($check['version']))
                            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $check['version'] }}</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- .env summary --}}
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden mb-8">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Environment</h2>
        </div>
        <div class="px-5 py-3">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                @foreach ($envInfo as $key => $value)
                    <dt class="text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $key }}</dt>
                    <dd class="font-mono text-xs">{{ $value }}</dd>
                @endforeach
            </dl>
        </div>
    </div>

    {{-- Vite / Tailwind / JS proof --}}
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden mb-8">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Frontend</h2>
        </div>
        <div class="px-5 py-4 space-y-4">
            {{-- Tailwind proof --}}
            <div>
                <p class="text-sm font-medium mb-2">Tailwind CSS</p>
                <div class="flex gap-2">
                    <span class="px-3 py-1 bg-blue-500 text-white text-xs rounded">blue-500</span>
                    <span class="px-3 py-1 bg-green-500 text-white text-xs rounded">green-500</span>
                    <span class="px-3 py-1 bg-red-500 text-white text-xs rounded">red-500</span>
                    <span class="px-3 py-1 bg-yellow-400 text-black text-xs rounded">yellow-400</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">If colored — Tailwind works via Vite</p>
            </div>

            {{-- JS proof --}}
            <div>
                <p class="text-sm font-medium mb-2">JavaScript (app.js → axios)</p>
                <div id="js-check" class="text-xs text-gray-400">Checking...</div>
            </div>

            {{-- HMR indicator --}}
            <div>
                <p class="text-sm font-medium mb-2">Vite HMR</p>
                <div id="hmr-check" class="text-xs text-gray-400">Checking...</div>
            </div>
        </div>
    </div>

    {{-- Axios test --}}
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Axios HTTP</h2>
        </div>
        <div class="px-5 py-4">
            <div id="axios-check" class="text-xs text-gray-400">Testing request...</div>
        </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-8">Blade rendered at {{ now()->format('H:i:s.u') }}</p>
</div>

<script type="module">
    // JS check
    const jsEl = document.getElementById('js-check');
    if (window.axios) {
        jsEl.innerHTML = '<span class="text-green-600 dark:text-green-400">✓ axios loaded (window.axios available)</span>';
    } else {
        jsEl.innerHTML = '<span class="text-red-500">✗ axios not found on window</span>';
    }

    // HMR check
    const hmrEl = document.getElementById('hmr-check');
    const viteClient = document.querySelector('script[src*="@@vite/client"]');
    if (viteClient) {
        hmrEl.innerHTML = '<span class="text-green-600 dark:text-green-400">✓ HMR active (Vite client detected)</span>';
    } else {
        hmrEl.innerHTML = '<span class="text-yellow-600 dark:text-yellow-400">⚠ HMR not detected</span>';
    }

    // Axios test
    const axiosEl = document.getElementById('axios-check');
    if (window.axios) {
        window.axios.get('/')
            .then(res => {
                axiosEl.innerHTML = `<span class="text-green-600 dark:text-green-400">✓ GET / → ${res.status} (${res.headers['content-type']?.split(';')[0]})</span>`;
            })
            .catch(err => {
                axiosEl.innerHTML = `<span class="text-red-500">✗ ${err.message}</span>`;
            });
    } else {
        axiosEl.innerHTML = '<span class="text-red-500">✗ axios not available, skipping</span>';
    }

    // On-demand checks (AJAX)
    document.querySelectorAll('.health-check-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const type = btn.dataset.checkType;
            const li = document.getElementById('check-' + type);
            const dot = li.querySelector('.check-dot');
            let detailsEl = li.querySelector('.check-details');

            // Spinner
            btn.innerHTML = '<span class="inline-flex items-center gap-1"><svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></span>';
            btn.disabled = true;

            try {
                const res = await window.axios.get('/health-check/run', { params: { type } });
                const data = res.data;

                // Update dot color
                dot.classList.remove('bg-green-500', 'bg-red-500');
                dot.classList.add(data.ok ? 'bg-green-500' : 'bg-red-500');

                // Update details text
                const text = data.error || data.details || '';
                const isError = !!data.error;

                if (detailsEl) {
                    if (isError && detailsEl.tagName !== 'PRE') {
                        const pre = document.createElement('pre');
                        pre.className = 'check-details text-xs text-red-500 mt-1 ml-4 whitespace-pre-wrap break-all max-h-40 overflow-y-auto';
                        pre.textContent = text;
                        detailsEl.replaceWith(pre);
                    } else if (!isError && detailsEl.tagName === 'PRE') {
                        const p = document.createElement('p');
                        p.className = 'check-details text-xs text-gray-500 dark:text-gray-400 mt-0.5 ml-4';
                        p.textContent = text;
                        detailsEl.replaceWith(p);
                    } else {
                        detailsEl.textContent = text;
                        detailsEl.className = isError
                            ? 'check-details text-xs text-red-500 mt-1 ml-4 whitespace-pre-wrap break-all max-h-40 overflow-y-auto'
                            : 'check-details text-xs text-gray-500 dark:text-gray-400 mt-0.5 ml-4';
                    }
                } else if (text) {
                    const el = isError ? document.createElement('pre') : document.createElement('p');
                    el.className = isError
                        ? 'check-details text-xs text-red-500 mt-1 ml-4 whitespace-pre-wrap break-all max-h-40 overflow-y-auto'
                        : 'check-details text-xs text-gray-500 dark:text-gray-400 mt-0.5 ml-4';
                    el.textContent = text;
                    li.querySelector('.min-w-0').appendChild(el);
                }

                btn.textContent = 'Run again';
            } catch (err) {
                btn.textContent = 'Error — retry';
            }

            btn.disabled = false;
        });
    });
</script>
</body>
</html>

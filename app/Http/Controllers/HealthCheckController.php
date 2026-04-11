<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class HealthCheckController extends Controller
{
    public function __invoke()
    {
        $checks = [];

        // PHP
        $checks['php'] = [
            'name' => 'PHP',
            'version' => PHP_VERSION,
            'ok' => true,
        ];

        // PHP Extensions
        $requiredExtensions = [
            'ctype', 'curl', 'dom', 'fileinfo', 'filter',
            'hash', 'mbstring', 'openssl', 'pcre', 'pdo',
            'pdo_mysql', 'session', 'tokenizer', 'xml',
            'redis', 'zip',
        ];
        $extensions = [];
        foreach ($requiredExtensions as $ext) {
            $extensions[$ext] = extension_loaded($ext);
        }
        $checks['php_extensions'] = [
            'name' => 'PHP Extensions',
            'details' => $extensions,
            'ok' => !in_array(false, $extensions, true),
        ];

        // Laravel
        $checks['laravel'] = [
            'name' => 'Laravel',
            'version' => app()->version(),
            'ok' => true,
        ];

        // Composer
        $composerVersion = null;
        try {
            $composerVersion = trim(shell_exec('composer --version 2>/dev/null') ?? '');
            $composerVersion = $composerVersion ?: null;
        } catch (\Throwable $e) {
        }
        $checks['composer'] = [
            'name' => 'Composer',
            'version' => $composerVersion,
            'ok' => $composerVersion !== null,
        ];

        // Nginx
        $nginxVersion = $_SERVER['SERVER_SOFTWARE'] ?? null;
        $checks['nginx'] = [
            'name' => 'Nginx',
            'version' => $nginxVersion ?: 'working (version via response header)',
            'ok' => true,
        ];

        // MySQL
        $mysqlOk = false;
        $mysqlVersion = null;
        $mysqlError = null;
        try {
            $mysqlVersion = DB::selectOne('SELECT VERSION() as v')->v;
            $mysqlOk = true;
        } catch (\Throwable $e) {
            $mysqlError = $e->getMessage();
        }
        $checks['mysql'] = [
            'name' => 'MySQL',
            'version' => $mysqlVersion,
            'ok' => $mysqlOk,
            'error' => $mysqlError,
        ];

        // Eloquent ORM
        $eloquentOk = false;
        $eloquentResult = null;
        $eloquentError = null;
        try {
            $count = User::count();
            $eloquentResult = "User::count() = {$count}";
            $eloquentOk = true;
        } catch (\Throwable $e) {
            $eloquentError = $e->getMessage();
        }
        $checks['eloquent'] = [
            'name' => 'Eloquent ORM',
            'details' => $eloquentResult,
            'ok' => $eloquentOk,
            'error' => $eloquentError,
        ];

        // Redis
        $redisOk = false;
        $redisVersion = null;
        $redisError = null;
        try {
            $pong = Redis::ping();
            $redisOk = ($pong === true || $pong === '+PONG' || $pong === 'PONG');

            $info = Redis::info('server');
            $redisVersion = $info['redis_version'] ?? ($info['Server']['redis_version'] ?? null);
        } catch (\Throwable $e) {
            $redisError = $e->getMessage();
        }
        $checks['redis'] = [
            'name' => 'Redis',
            'version' => $redisVersion,
            'ok' => $redisOk,
            'error' => $redisError,
        ];

        // Redis read/write
        $redisRwOk = false;
        $redisRwError = null;
        try {
            $testKey = 'health_check_test_' . time();
            Redis::set($testKey, 'ok', 'EX', 5);
            $val = Redis::get($testKey);
            Redis::del($testKey);
            $redisRwOk = ($val === 'ok');
        } catch (\Throwable $e) {
            $redisRwError = $e->getMessage();
        }
        $checks['redis_rw'] = [
            'name' => 'Redis Read/Write',
            'details' => $redisRwOk ? 'SET → GET → DEL OK' : null,
            'ok' => $redisRwOk,
            'error' => $redisRwError,
        ];

        // Cache
        $cacheOk = false;
        $cacheError = null;
        try {
            $cacheKey = 'health_check_cache_' . time();
            Cache::put($cacheKey, 'works', 5);
            $cacheOk = Cache::get($cacheKey) === 'works';
            Cache::forget($cacheKey);
        } catch (\Throwable $e) {
            $cacheError = $e->getMessage();
        }
        $checks['cache'] = [
            'name' => 'Cache (' . config('cache.default') . ')',
            'ok' => $cacheOk,
            'error' => $cacheError,
        ];

        // Session
        $checks['session'] = [
            'name' => 'Session',
            'details' => 'driver: ' . config('session.driver'),
            'ok' => true,
        ];

        // Queue
        $checks['queue'] = [
            'name' => 'Queue',
            'details' => 'connection: ' . config('queue.default'),
            'ok' => true,
        ];

        // Storage
        $storageOk = false;
        $storageError = null;
        try {
            $testFile = 'health_check_test.txt';
            Storage::put($testFile, 'ok');
            $storageOk = Storage::get($testFile) === 'ok';
            Storage::delete($testFile);
        } catch (\Throwable $e) {
            $storageError = $e->getMessage();
        }
        $checks['storage'] = [
            'name' => 'Storage (write)',
            'ok' => $storageOk,
            'error' => $storageError,
        ];

        // Blade
        $checks['blade'] = [
            'name' => 'Blade',
            'details' => 'rendering this page',
            'ok' => true,
        ];

        // Vite
        $viteHot = file_exists(public_path('hot'));
        $viteManifest = file_exists(public_path('build/manifest.json'));
        $checks['vite'] = [
            'name' => 'Vite',
            'details' => $viteHot ? 'dev server (HMR active)' : ($viteManifest ? 'production build' : 'not running'),
            'ok' => $viteHot || $viteManifest,
        ];

        // Xdebug
        $xdebugLoaded = extension_loaded('xdebug');
        $xdebugMode = ini_get('xdebug.mode') ?: 'off';
        $xdebugHost = ini_get('xdebug.client_host') ?: 'N/A';
        $xdebugPort = ini_get('xdebug.client_port') ?: 'N/A';
        $xdebugStart = ini_get('xdebug.start_with_request') ?: 'N/A';
        $xdebugVersion = $xdebugLoaded ? phpversion('xdebug') : null;
        $checks['xdebug'] = [
            'name' => 'Xdebug',
            'version' => $xdebugVersion,
            'details' => $xdebugLoaded
                ? "mode: {$xdebugMode} | host: {$xdebugHost}:{$xdebugPort} | start: {$xdebugStart}"
                : 'not installed',
            'ok' => $xdebugLoaded && $xdebugMode !== 'off',
        ];

        // APP_KEY
        $appKey = config('app.key');
        $checks['app_key'] = [
            'name' => 'APP_KEY',
            'details' => $appKey ? 'set (' . substr($appKey, 0, 10) . '...)' : 'NOT SET',
            'ok' => !empty($appKey),
        ];

        // Migrations (on-demand via AJAX)
        $checks['migrations'] = [
            'name' => 'Migrations',
            'details' => 'not checked',
            'ok' => true,
            'pending' => true,
        ];

        // PHP Limits
        $checks['php_limits'] = [
            'name' => 'PHP Limits',
            'details' => 'memory_limit: ' . ini_get('memory_limit')
                . ' | max_execution_time: ' . ini_get('max_execution_time') . 's'
                . ' | upload_max_filesize: ' . ini_get('upload_max_filesize')
                . ' | post_max_size: ' . ini_get('post_max_size'),
            'ok' => true,
        ];

        // Timezone
        $phpTz = date_default_timezone_get();
        $mysqlTz = null;
        try {
            $mysqlTzRow = DB::selectOne("SELECT @@global.time_zone as tz");
            $mysqlTz = $mysqlTzRow->tz;
        } catch (\Throwable $e) {
        }
        $checks['timezone'] = [
            'name' => 'Timezone',
            'details' => "PHP: {$phpTz} | MySQL: " . ($mysqlTz ?? 'N/A'),
            'ok' => true,
        ];

        // Disk space
        $freeBytes = disk_free_space('/var/www/html');
        $totalBytes = disk_total_space('/var/www/html');
        $freeGb = round($freeBytes / 1024 / 1024 / 1024, 1);
        $totalGb = round($totalBytes / 1024 / 1024 / 1024, 1);
        $usedPercent = round((1 - $freeBytes / $totalBytes) * 100);
        $checks['disk'] = [
            'name' => 'Disk Space',
            'details' => "{$freeGb} GB free / {$totalGb} GB total ({$usedPercent}% used)",
            'ok' => $freeGb > 1,
        ];

        // Log writable
        $logPath = storage_path('logs');
        $logWritable = false;
        $logError = null;
        try {
            $testLog = $logPath . '/health_check_test.log';
            file_put_contents($testLog, 'ok');
            $logWritable = file_get_contents($testLog) === 'ok';
            unlink($testLog);
        } catch (\Throwable $e) {
            $logError = $e->getMessage();
        }
        $checks['log_writable'] = [
            'name' => 'Log Writable',
            'details' => $logWritable ? storage_path('logs') : null,
            'ok' => $logWritable,
            'error' => $logError,
        ];

        // Scheduled Tasks (on-demand via AJAX)
        $checks['schedule'] = [
            'name' => 'Scheduled Tasks',
            'details' => 'not checked',
            'ok' => true,
            'pending' => true,
        ];

        // Tests (on-demand via AJAX)
        $checks['tests'] = [
            'name' => 'PHPUnit Tests',
            'details' => 'not checked',
            'ok' => true,
            'pending' => true,
        ];

        // .env summary
        $envInfo = [
            'APP_ENV' => config('app.env'),
            'APP_DEBUG' => config('app.debug') ? 'true' : 'false',
            'DB_CONNECTION' => config('database.default'),
            'CACHE_STORE' => config('cache.default'),
            'SESSION_DRIVER' => config('session.driver'),
            'QUEUE_CONNECTION' => config('queue.default'),
            'REDIS_CLIENT' => config('database.redis.client'),
        ];

        $allOk = collect($checks)->every(fn ($c) => $c['ok']);

        return view('welcome', compact('checks', 'envInfo', 'allOk'));
    }

    public function runCheck()
    {
        $type = request()->query('type');

        return match ($type) {
            'tests' => $this->runTests(),
            'migrations' => $this->runMigrations(),
            'schedule' => $this->runSchedule(),
            default => response()->json(['error' => 'Unknown check type'], 400),
        };
    }

    private function runTests(): \Illuminate\Http\JsonResponse
    {
        $output = [];
        $exitCode = null;
        exec('cd /var/www/html && php artisan test --no-ansi 2>&1', $output, $exitCode);

        return response()->json([
            'ok' => $exitCode === 0,
            'details' => $exitCode === 0 ? 'All tests passed' : null,
            'error' => $exitCode === 0 ? null : implode("\n", $output),
        ]);
    }

    private function runMigrations(): \Illuminate\Http\JsonResponse
    {
        $output = [];
        $exitCode = null;
        exec('cd /var/www/html && php artisan migrate:status --no-ansi 2>&1', $output, $exitCode);
        $migrationOutput = implode("\n", $output);
        $pendingCount = substr_count($migrationOutput, 'Pending');

        return response()->json([
            'ok' => $pendingCount === 0,
            'details' => $pendingCount > 0 ? "{$pendingCount} pending" : 'All applied',
            'error' => $pendingCount > 0 ? $migrationOutput : null,
        ]);
    }

    private function runSchedule(): \Illuminate\Http\JsonResponse
    {
        $output = [];
        $exitCode = null;
        exec('cd /var/www/html && php artisan schedule:list --no-ansi 2>&1', $output, $exitCode);
        $scheduleOutput = implode("\n", $output);
        $hasSchedule = !str_contains($scheduleOutput, 'No scheduled commands')
            && !str_contains($scheduleOutput, 'No scheduled tasks');

        return response()->json([
            'ok' => true,
            'details' => $hasSchedule ? trim($scheduleOutput) : 'No scheduled commands',
        ]);
    }
}

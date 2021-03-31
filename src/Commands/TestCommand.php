<?php

namespace Internexus\WatcherLaravel\Commands;

use Internexus\WatcherLaravel\Facades\Watcher;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watcher:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send data to your dashboard.';

    /**
     * Execute the console command.
     *
     * @param Repository $config
     * @return void
     * @throws \Throwable
     */
    public function handle(Repository $config)
    {
        if (! Watcher::isCapturing()) {
            $this->warn('Watcher is not enabled');
            return;
        }

        $this->line("I'm testing your Watcher integration.");

        // Test proc_open function availability
        try {
            proc_open("", [], $pipes);
        } catch (\Throwable $exception) {
            $this->warn("❌ proc_open function disabled.");
            return;
        }

        ! empty($config->get('watcher.key'))
                ? $this->info('✅ Watcher key installed.')
                : $this->warn('❌ Watcher key not specified. Make sure you specify the WATCHER_KEY in your .env file.');

        function_exists('curl_version')
                ? $this->info('✅ CURL extension is enabled.')
                : $this->warn('❌ CURL is actually disabled so your app could not be able to send data to Watcher.');

    }
}

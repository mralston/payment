<?php

namespace Mralston\Payment\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GetEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:get-env';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Output the current composer configuration mode for the payment package.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $composerPath = base_path('composer.json');
        if (! File::exists($composerPath)) {
            $this->error('composer.json not found in application root.');

            return 1;
        }

        $composer = json_decode(File::get($composerPath), true);
        $repositories = $composer['repositories'] ?? [];
        $currentMode = 'prod';

        foreach ($repositories as $repo) {
            if (isset($repo['url']) && str_contains($repo['url'], 'mralston/payment')) {
                $currentMode = 'dev';
                break;
            }
        }

        $this->line("Current mode: <info>{$currentMode}</info>");

        return 0;
    }
}

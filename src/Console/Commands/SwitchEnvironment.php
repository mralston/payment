<?php

namespace Mralston\Payment\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\select;

class SwitchEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:switch-env {environment? : The environment to switch to (prod, dev, toggle)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Switch the payment package between local development (symlinked) and production (standard) composer configurations.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $environment = $this->argument('environment');

        if (! $environment) {
            $this->call('payment:get-env');
            $this->newLine();

            $environment = select(
                label: 'Which environment would you like to switch to?',
                options: [
                    'prod' => 'Production (Standard)',
                    'dev' => 'Development (Symlinked)',
                    'toggle' => 'Toggle current',
                ],
                default: 'toggle'
            );
        }

        if (! in_array($environment, ['prod', 'dev', 'toggle'])) {
            $this->error('Invalid environment specified. Use prod, dev, or toggle.');

            return 1;
        }

        $composerPath = base_path('composer.json');
        if (! File::exists($composerPath)) {
            $this->error('composer.json not found in application root.');

            return 1;
        }

        $composer = json_decode(File::get($composerPath), true);
        $currentMode = $this->getCurrentMode($composer);

        if ($environment === 'toggle') {
            $environment = ($currentMode === 'dev') ? 'prod' : 'dev';
        }

        if ($environment === $currentMode) {
            $this->info("Already in {$environment} mode.");

            return 0;
        }

        if ($environment === 'dev') {
            $this->switchToDev($composer);
        } else {
            $this->switchToProd($composer);
        }

        File::put($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info("composer.json updated to {$environment} mode.");

        $this->info('Running composer update mralston/payment...');

        $result = Process::path(base_path())
            ->timeout(300)
            ->run('composer update mralston/payment');

        if ($result->successful()) {
            $this->info('Composer update successful.');
            $this->info($result->output());

            return 0;
        } else {
            $this->error('Composer update failed.');
            $this->error($result->errorOutput());

            return 1;
        }
    }

    protected function getCurrentMode(array $composer): string
    {
        $repositories = $composer['repositories'] ?? [];
        foreach ($repositories as $repo) {
            if (isset($repo['url']) && str_contains($repo['url'], 'mralston/payment')) {
                return 'dev';
            }
        }

        return 'prod';
    }

    protected function switchToDev(array &$composer): void
    {
        $composer['require']['mralston/payment'] = 'dev-main';

        $repositories = $composer['repositories'] ?? [];
        $found = false;
        foreach ($repositories as $repo) {
            if (isset($repo['url']) && str_contains($repo['url'], 'mralston/payment')) {
                $found = true;
                break;
            }
        }

        if (! $found) {
            $repositories[] = [
                'type' => 'path',
                'url' => '../packages/mralston/payment',
                'options' => [
                    'symlink' => true,
                ],
            ];
        }

        $composer['repositories'] = $repositories;
    }

    protected function switchToProd(array &$composer): void
    {
        $composer['require']['mralston/payment'] = '^1.0';

        if (isset($composer['repositories'])) {
            $composer['repositories'] = array_values(array_filter($composer['repositories'], function ($repo) {
                return ! (isset($repo['url']) && str_contains($repo['url'], 'mralston/payment'));
            }));

            if (empty($composer['repositories'])) {
                unset($composer['repositories']);
            }
        }
    }
}

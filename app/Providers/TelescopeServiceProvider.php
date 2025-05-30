<?php

namespace App\Providers;

use App\Notifications\ExceptionNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\IncomingExceptionEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            if ($isLocal || !!config('telescope.log_all', false)) {
                return true;
            }

            return $isLocal ||
                $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
        
        Telescope::afterStoring(function (array $entries, string $batchId) {

            if (config('app.env', 'local') == 'local')
                return;

            /** @var IncomingExceptionEntry|IncomingEntry $entry */
            foreach ($entries as $entry) {

                if (!(
                    $entry->isReportableException() ||
                    $entry->isFailedRequest() ||
                    $entry->isFailedJob() ||
                    $entry->hasMonitoredTag()
                )) {
                    continue;
                }

                $url = "telescope/";

                $url .= match (true) {
                    $entry->isRequest() => "requests",
                    $entry->isFailedJob() => "jobs",
                    $entry->isReportableException() => "exceptions",
                    default => "requests"
                };

                try {
                    Notification::route('mail', "dev_notifications@alletrons.tech")
                        ->notify(new ExceptionNotification(
                            $entry,
                            [
                                'environment' => app()->environment(),
                                'url' => app()->runningInConsole() ? 'CLI' : request()->method() . ' ' . request()->fullUrl(),
                                'user' => $entry->content['user'] ?? '-',
                                'view in Telescope' => url("$url/{$entry->uuid}")
                            ]
                        ));

                    Log::channel('slack')->critical(
                        $entry instanceof IncomingExceptionEntry ? $entry->exception : "There has been an error - " .  (app()->environment()['url'] ?? ""),
                        [
                            'environment' => app()->environment(),
                            'url' => app()->runningInConsole() ? 'CLI' : request()->method() . ' ' . request()->fullUrl(),
                            'user' => $entry->content['user'] ?? '-',
                            'view in Telescope' => url("$url/{$entry->uuid}")
                        ]
                    );

                    Artisan::call('optimize:clear');
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local') || !!config('telescope.log_all', false)) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user = null) {

            return true;
            /*   return in_array($user->email, [
                
            ]); */
        });
    }
}

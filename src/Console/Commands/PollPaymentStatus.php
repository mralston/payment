<?php

namespace Mralston\Payment\Console\Commands;

use Mralston\Payment\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PollPaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:status-poll {payment?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Polls the payment provider for updated payment statuses.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Were we given an application to poll explicitly?
        if (!empty($this->argument('payment'))) {
            $payment = Payment::find($this->argument('payment'));
            if (!empty($payment)) {
                Log::channel('payment')->debug('Polling payment #' . $payment->id);
                try {
                    $payment
                        ->paymentProvider
                        ->gateway()
                        ->pollStatus($payment);
                    Log::channel('payment')->debug($this->signature . ': Polled status of payment #' . $payment->id . '.');
                    Log::channel('payment')->debug($this->signature . ': ' . $payment->status);
                } catch (\Exception $ex) {
                    Log::channel('payment')->error($this->signature . ': Unable to poll status of payment #' . $payment->id . '.');
                    Log::channel('finance')->error($this->signature . ': Error #' . $ex->getCode() . ': ' . $ex->getMessage());
                }

                return 0;
            } else {
                Log::channel('finance')->debug('Unable to find payment #' . $this->argument('payment') . ' for polling.');
                return 1;
            }
        }

        // Poll all applications in intermediate states,
        // or recently declined (sometimes there are appeals).
        // Declined applications get trashed, so we need to allow for trashed declines,
        // but non-declines cannot be trashed
        Payment::withTrashed()
            ->where(function ($query) {
                $query->whereIn('status', [
                    'pending',
                    'referred',
                    'accepted',
                    'conditional_accept',
                    'parked'
                ])->whereNull('deleted_at');
            })->orWhere(function ($query) {
                $query->where('status', 'declined')
                    ->where('decision_received_at', '>', Carbon::now()->subtract(14, 'days'));
            })->chunk(10, function ($payments) {
                $payments->map(function (Payment $payment) {
                    Log::channel('finance')->debug('Polling payment #' . $payment->id);
                    try {
                        $payment
                            ->paymentProvider
                            ->gateway()
                            ->pollStatus($payment);

                        Log::channel('payment')->debug($this->signature . ': Polled status of application #' . $payment->id . '.');
                        Log::channel('payment')->debug($this->signature . ': ' . $payment->status);
                    } catch (\Exception $ex) {
                        Log::channel('payment')->error($this->signature . ': Unable to poll status of payment #' . $payment->id . '.');
                        Log::channel('payment')->error($this->signature . ': Error #' . $ex->getCode() . ': ' . $ex->getMessage());
                    }
                });
            });

        return 0;
    }
}

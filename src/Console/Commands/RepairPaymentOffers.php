<?php

namespace Mralston\Payment\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mralston\Payment\Models\PaymentOffer;
use Mralston\Payment\Models\PaymentSurvey;

class RepairPaymentOffers extends Command
{
    protected $signature = 'payment:repair-offers
                            {--dry : Dry run; don\'t persist changes}
                            {--fix-reference : Also adjust the reference prefix to the parentable id (before the dash)}';

    protected $description = 'Fix payment offers whose morph parent (parentable_*) does not match their survey\'s parent.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $fixReference = (bool) $this->option('fix-reference');

        $this->info(($dry ? '[DRY RUN] ' : '') . 'Scanning for mismatched offers...');

        $fixed = 0;
        $skipped = 0;

        // Chunk to keep memory in check
        PaymentOffer::query()
            ->with(['paymentSurvey'])
            ->whereNotNull('payment_survey_id')
            ->where('parentable_id', '!=', 5124622)
            ->orderBy('id')
            ->chunkById(1000, function ($offers) use (&$fixed, &$skipped, $dry, $fixReference) {
                foreach ($offers as $offer) {
                    /** @var PaymentOffer $offer */
                    /** @var PaymentSurvey|null $survey */
                    $survey = $offer->paymentSurvey;
                    if (!$survey) {
                        $this->warn("Offer #{$offer->id} has no survey; skipping");
                        $skipped++;
                        continue;
                    }

                    $parent = $survey->parentable; // morph parent of the survey
                    if (!$parent) {
                        $this->warn("Survey #{$survey->id} has no parent for offer #{$offer->id}; skipping");
                        $skipped++;
                        continue;
                    }

                    $needsParentFix = ($offer->parentable_type ?? null) !== ($parent::class)
                        || (string) ($offer->parentable_id ?? '') !== (string) $parent->getKey();

                    $needsReferenceFix = false;
                    if ($fixReference && !empty($offer->reference)) {
                        // Expectation: first token before '-' should equal the parentable id
                        $prefix = Str::before($offer->reference, '-');
                        if ((string) $prefix !== (string) $parent->getKey()) {
                            $needsReferenceFix = true;
                        }
                    }

                    if (!$needsParentFix && !$needsReferenceFix) {
                        $skipped++;
                        continue;
                    }

                    $this->line(sprintf(
                        'Offer #%d: %s%s',
                        $offer->id,
                        $needsParentFix ? 'fixing parent' : 'parent ok',
                        $needsReferenceFix ? '; fixing reference' : ''
                    ));

                    if ($dry) {
                        continue;
                    }

                    DB::transaction(function () use ($offer, $parent, $needsParentFix, $needsReferenceFix) {
                        if ($needsParentFix) {
                            $offer->parentable()->associate($parent);
                        }
                        if ($needsReferenceFix) {
                            // Replace the prefix before the first dash with the new parent id
                            $offer->reference = $parent->getKey() . '-' . Str::after($offer->reference, '-');
                        }
                        $offer->save();
                    });

                    $fixed++;
                }
            });

        $this->info("Done. Fixed: {$fixed}; Skipped: {$skipped}");
        return self::SUCCESS;
    }
}

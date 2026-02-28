<?php

namespace Mralston\Payment\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class UkAddressParser
{
    protected array $counties = [
        'Aberdeenshire', 'Angus', 'Antrim', 'Argyll', 'Armagh', 'Avon', 'Ayrshire', 'Banffshire', 'Bedfordshire', 'Berkshire', 'Berwickshire', 'Buckinghamshire', 'Caithness', 'Cambridgeshire', 'Cheshire', 'Clackmannanshire', 'Cleveland', 'Clwyd', 'Cornwall', 'County Durham', 'Cumberland', 'Cumbria', 'Derbyshire', 'Devon', 'Dorset', 'Dumfriesshire', 'Dunbartonshire', 'Dundee', 'Down', 'Derry', 'East Lothian', 'East Sussex', 'Essex', 'Fermanagh', 'Fife', 'Gloucestershire', 'Greater London', 'Greater Manchester', 'Gwent', 'Gwynedd', 'Hampshire', 'Herefordshire', 'Hertfordshire', 'Huntingdonshire', 'Inverness-shire', 'Isle of Wight', 'Kent', 'Kincardineshire', 'Kinross-shire', 'Kirkcudbrightshire', 'Lanarkshire', 'Lancashire', 'Leicestershire', 'Lincolnshire', 'London', 'Londonderry', 'Merseyside', 'Mid Glamorgan', 'Middlesex', 'Midlothian', 'Moray', 'Nairnshire', 'Norfolk', 'Northamptonshire', 'Northumberland', 'North Yorkshire', 'Nottinghamshire', 'Orkney', 'Oxfordshire', 'Peeblesshire', 'Pembrokeshire', 'Perthshire', 'Powys', 'Renfrewshire', 'Ross-shire', 'Roxburghshire', 'Selkirkshire', 'Shetland', 'Shropshire', 'Somerset', 'South Glamorgan', 'South Yorkshire', 'Staffordshire', 'Stirlingshire', 'Suffolk', 'Surrey', 'Sussex', 'Sutherland', 'Tyne and Wear', 'Tyne & Wear', 'Tyrone', 'Warwickshire', 'West Glamorgan', 'West Lothian', 'West Midlands', 'West Sussex', 'West Yorkshire', 'Wigtownshire', 'Wiltshire', 'Worcestershire', 'Yorkshire'
    ];

    /**
     * Regex to identify a house number segment.
     * Matches: "61", "67a", "61-65", "Unit 1", "Flat 101"
     */
    protected string $houseNumberRegex = '/^((Unit|Flat|Apartment|Suite|Room|Rm)\s+)?\d+([a-zA-Z]|-\d+)?$/i';

    public function parse(string $rawAddress): array
    {
        // 1. Cleanup: Convert delimiters and cast Stringable to string correctly
        $cleanString = Str::of($rawAddress)
            ->replace([';', "\n", "\r"], ',')
            ->trim(', ')
            ->toString();

        $parts = collect(explode(',', $cleanString))
            ->map(fn($p) => trim($p))
            ->filter(fn($p) => !empty($p) && $p !== '.')
            ->values();

        $result = $this->getEmptySchema();

        // 2. Extract Postcode
        if ($parts->isNotEmpty() && preg_match('/[A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}$/i', $parts->last())) {
            $result['postCode'] = Str::upper($parts->pop());
        }

        // 3. Extract County
        if ($parts->isNotEmpty()) {
            $potentialCounty = $parts->last();
            if (collect($this->counties)->contains(fn($c) => strcasecmp($c, $potentialCounty) === 0)) {
                $result['county'] = $parts->pop();
            }
        }

        // 4. Extract Town
        if ($parts->isNotEmpty()) {
            $result['town'] = $parts->pop();
        }

        // 5. Pre-Parse: Handle segments that combine Number + Street (e.g. "67a New Street")
        $parts = $parts->flatMap(function($part) {
            // If segment starts with a number but has a space and letters
            if (preg_match('/^(\d+[a-zA-Z]?)\s+([a-zA-Z].+)$/', $part, $matches)) {
                return [$matches[1], $matches[2]];
            }
            return [$part];
        });

        // 6. Locate the House Number Anchor
        $numberIndex = $parts->search(fn($part) => preg_match($this->houseNumberRegex, $part));

        if ($numberIndex !== false) {
            $result['houseNumber'] = $parts->get($numberIndex);

            // Everything before the number is sub-building/org (Address 1)
            $result['address1'] = $parts->slice(0, $numberIndex)->implode(', ');

            // The element immediately after the number is the Street
            $result['street'] = $parts->get($numberIndex + 1);

            // Remaining is Address 2 / 3
            $remaining = $parts->slice($numberIndex + 2);
            $result['address2'] = $remaining->shift();
            $result['address3'] = $remaining->implode(', ');
        } else {
            // Fallback for named buildings / non-numeric addresses
            $result['address1'] = $parts->shift();
            $result['street'] = $parts->shift() ?? $result['address1'];
            $result['address2'] = $parts->shift();
            $result['address3'] = $parts->implode(', ');
        }

        return $this->finalize($result);
    }

    private function finalize(array $result): array
    {
        // If address1 is empty (no company/flat prefix), move street up to satisfy lenders
        if (empty($result['address1'])) {
            $result['address1'] = $result['street'];
        }

        return collect($result)->map(fn($v) => filled($v) ? (string)$v : null)->toArray();
    }

    private function getEmptySchema(): array
    {
        return [
            'houseNumber' => null, 'street' => null, 'address1' => null,
            'address2' => null, 'address3' => null, 'town' => null,
            'county' => null, 'postCode' => null,
        ];
    }
}

<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mralston\Payment\Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function payloadForSendingApplicationRequest(string $mediaCode): array
{
    return [
        "environmentCode" => "UAT",
        "apiFormCode" => "S03_RETAIL",
        "hostSystemApplicationRef" => time() . Str::random(10),
        "loan" => [
          "mediaCode" => $mediaCode,
          "productCode" => "IBC22",
          "introducersReference" => "ABC5461",
          "goodsHostCode" => "WINDDOOR",
          "loanPurposeHostCode" => "HOMEIMPROV",
          "cashPriceAmount" => 5500,
          "depositAmount" => 500,
          "additionalContribution" => 0,
          "loanAmount" => 5000,
          "repaymentTermInMonths" => 36,
          "partExchangeAmount" => 0,
          "settlementValueAmount" => 0,
          "consentToCreditSearch" => true,
          "consentToTerms" => true,
          "preferredPaymentDay" => 8,
          "salespersonEmail" => "a@a.com",
          "installerName" => "Simon Shaw",
          "assetType" => "STATIC_CARAVAN",
          "assetMake" => "Willerby",
          "assetModel" => "Rio",
          "assetSerialNumber" => "1234567890ABCDEFG",
          "assetCondition" => "NEW",
          "notes" => "Additional notes",
          "alertNotes" => "This is an alert note text when the app was created"
        ],
        "applicant" => [
          "titleCode" => "MR",
          "firstName" => "Citlalli",
          "middleName" => "Lois",
          "lastName" => "Hill",
          "dateOfBirth" => "1976-03-11",
          "email" => "a@a.com",
          "mobilePhone" => "07777777777",
          "homePhone" => "02222222222",
          "residentialStatusCode" => "H",
          "maritalStatusCode" => "M",
          "numberOfDependents" => "2",
          "isVulnerable" => true,
          "vulnerableReason" => "reason",
          "employmentStatusCode" => "E",
          "employmentEmployerName" => "Aryza",
          "employmentOccupation" => "Postman",
          "employmentTimeYears" => "5",
          "employmentTimeMonths" => "2"
        ],
        "currentAddress" => [
          "buildingSubName" => "Flat 2A",
          "buildingName" => "Trident House",
          "buildingNo" => "25",
          "addressLine1" => "28 Alley Cat Lane",
          "addressLine2" => "Test Town",
          "city" => "Test Town",
          "county" => "South Glamorgan",
          "postcode" => "X9 9AA",
          "timeYears" => "5",
          "timeMonths" => "2"
        ],
        "previousAddress1" => [
          "buildingSubName" => "Flat 2A",
          "buildingName" => "Trident House",
          "buildingNo" => "25",
          "addressLine1" => "28 Alley Cat Lane",
          "addressLine2" => "Test Town",
          "city" => "Test Town",
          "county" => "South Glamorgan",
          "postcode" => "X9 9AA",
          "timeYears" => "5",
          "timeMonths" => "2"
        ],
        "previousAddress2" => [
          "buildingSubName" => "Flat 2A",
          "buildingName" => "Trident House",
          "buildingNo" => "25",
          "addressLine1" => "28 Alley Cat Lane",
          "addressLine2" => "Test Town",
          "city" => "Test Town",
          "county" => "South Glamorgan",
          "postcode" => "X9 9AA",
          "timeYears" => "5",
          "timeMonths" => "2"
        ],
        "installationAddress" => [
          "buildingSubName" => "Flat 2A",
          "buildingName" => "Trident House",
          "buildingNo" => "25",
          "addressLine1" => "28 Alley Cat Lane",
          "addressLine2" => "Test Town",
          "city" => "Test Town",
          "county" => "South Glamorgan",
          "postcode" => "X9 9AA",
          "timeYears" => "5",
          "timeMonths" => "2"
        ],
        "bankDetails" => [
          "bankSortCode" => "999998",
          "bankAccountNumber" => "29250380",
          "bankAccountHolderName" => "Mr A Franks",
          "bankName" => "Llloyds Bank",
          "bankTimeYears" => "5",
          "bankTimeMonths" => "2"
        ],
        "incomeExpenditure" => [
          "grossAnnualSalary" => 2000,
          "grossHouseholdIncome" => 2500,
          "netMonthlySalary" => 1500,
          "netMonthlyIncomeBenefits" => 0,
          "netMonthlyIncomePension" => 0,
          "netMonthlyIncomeOtherSources" => 0,
          "monthlyExpensesRentOrMortgage" => 550,
          "monthlyExpensesOther" => 250.89,
          "expectAnySignificantIncomeChg" => false,
          "expectAnySignificantIncomeChgReason" => "reason"
        ]
    ];
}


<?php

namespace Mralston\Payment\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mralston\Payment\Data\AddressData;
use Mralston\Payment\Data\CompanyData;

interface PaymentHelper
{
    public function setParentModel(Model $parentModel): static;

    public function getViewData(): array;

    public function getCustomers(): Collection;

    public function getAddress(): AddressData;

    public function getParentRouteName(): string;

    public function getParentRoute(): string;

    public function getTotalCost(): float;

    public function getBasketItems(): array;

    public function hasFeature(string $feature): bool;

    public function getSem(): float;

    public function getSystemSavings(): Collection;

    public function getSolarSavingsYear1(): float;

    public function getBatterySavingsYear1(): float;

    public function getNet(): float;

    public function getVatRate(): float;

    public function getVat(): float;

    public function getGross(): float;

    public function getReference(): string;

    public function getLeaseContent(): ?string;

    public function getCompanyDetails(): CompanyData;

    public function storeFile(string $filename, string $contents): Object;

    public function disablePaymentProcess(): bool|string;

    public function getApiKey(string $provider): ?string;
}

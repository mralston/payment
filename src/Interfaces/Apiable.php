<?php

namespace Mralston\Payment\Interfaces;

interface Apiable
{
    public function getCancellationResponse(): array;
}
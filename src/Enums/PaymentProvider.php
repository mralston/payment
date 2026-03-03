<?php

namespace Mralston\Payment\Enums;

enum PaymentProvider: string
{
    case TANDEM = 'tandem';
    case PROPENSIO = 'propensio';
    case IKANO = 'ikano';
    case OMNI = 'omni';
    case HOMETREE = 'hometree';
}
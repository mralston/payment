<?php

namespace Mralston\Payment\Enums;

enum LookupField: string
{
    case MARITAL_STATUS = 'marital_status';
    case HOMEOWNER = 'homeowner';
    case MORTGAGE = 'mortgage';
    case EMPLOYMENT_STATUS = 'employment_status';
    case BRITISH_CITIZEN = 'british_citizen';
}

<?php

namespace Mralston\Payment\Enums;

enum LookupField: string
{
    case MARITAL_STATUS = 'marital_status';
    case RESIDENTIAL_STATUS = 'residential_status';
    case EMPLOYMENT_STATUS = 'employment_status';
    case NATIONALITY = 'nationality';
    case BANKRUPT_OR_IVA = 'bankrupt_or_iva';
    case CURRENT_ACCOUNT_FOR_BUSINESS = 'current_account_for_business';
    case TITLE = 'title';
}

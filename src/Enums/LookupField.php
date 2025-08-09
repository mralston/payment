<?php

namespace Mralston\Payment\Enums;

enum LookupField: string
{
    case MARITAL_STATUS = 'marital_status';
    case RESIDENTIAL_STATUS = 'residential_status';
    case EMPLOYMENT_STATUS = 'employment_status';
    case NATIONALITY = 'nationality';
}

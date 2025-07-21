<?php

namespace Mralston\Payment\Enums;

enum EmploymentStatus: int
{
    case FULL_TIME = 100;
    case PART_TIME = 200;
    case CASUAL = 300;
    case SELF_EMPLOYED = 400;
    case HOUSEHOLD_CARER = 500;
    case RETIRED = 600;
    case UNEMPLOYED = 700;
}

<?php

namespace Mralston\Payment\Enums;

enum PaymentType: string
{
    case CASH = 'cash';
    case FINANCE = 'finance';
    case LEASE = 'lease';
}

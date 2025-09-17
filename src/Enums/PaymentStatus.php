<?php

namespace Mralston\Payment\Enums;

enum PaymentStatus: string
{
    case ACCEPTED = 'accepted';
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case CONDITIONAL_ACCEPT = 'conditional_accept';
    case CUSTOMER_CANCELLED = 'customer_cancelled';
    case DECLINED = 'declined';
    case DOCUMENTS_RECEIVED = 'documents_received';
    case ERROR = 'error';
    case EXPIRED = 'expired';
    case LIVE = 'live';
    case NEW = 'new';
    case NOT_FOUND = 'not_found';
    case PARKED = 'parked';
    case PAYOUT_REQUESTED = 'payout_requested';
    case PENDING = 'pending';
    case REFERRED = 'referred';
    case SNAGGED = 'snagged';
}

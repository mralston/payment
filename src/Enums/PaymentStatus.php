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

    case HOMETREE_PENDING_APPLICANTS = 'pending-applicants';
    case HOMETREE_PENDING_CUSTOMER_CHOICE = 'pending-customer-choice';
    case HOMETREE_PENDING_UNDERWRITING_REVIEW = 'pending-underwriting-review';
    case HOMETREE_PENDING_CUSTOMER_DATA = 'pending-customer-data';
    case HOMETREE_PENDING_CUSTOMER_AGREEMENT = 'pending-customer-agreement';
    case HOMETREE_PENDING_DOCUMENTATION_REVIEW = 'pending-documentation-review';
    case HOMETREE_PENDING_INSTALLATION = 'pending-installation';
    case HOMETREE_PENDING_QUOTE_REVIEW = 'pending-quote-review';
    case HOMETREE_PROCESSING = 'processing';
    case HOMETREE_ACTIVE_INSTALLED = 'active-installed';
    case HOMETREE_ACTIVE_COMPLETED = 'active-completed';
    case HOMETREE_FINAL_DECLINED = 'final-declined';
    case HOMETREE_FINAL_CANCELLED = 'final-cancelled';
    case HOMETREE_FINAL_ENDED = 'final-ended';
    case HOMETREE_FINAL_ABANDONED = 'final-abandoned';
    case HOMETREE_FINAL_ARCHIVED = 'final-archived';
    case HOMETREE_INVALID = 'invalid';

    case PROPENSIO_CUSTOMER_NPW = 'WSFcustomernpw';
    case PROPENSIO_PENDING_FURTHER_INFO = 'WSFpendingfurtherinf';
    case PROPENSIO_EXECUTED = 'WSFexecuted';
    case PROPENSIO_SANCTION = 'WSFsanction';
    case PROPENSIO_AUTO_ACCEPT = 'WSFautoaccept';
    case PROPENSIO_AUTO_DECLINE = 'WSFautodecline';
    case PROPENSIO_MANUAL_ACCEPT = 'WSFmanualaccept';
    case PROPENSIO_MANUAL_DECLINE = 'WSFmanualdecline';
    case PROPENSIO_MANUAL_REFER = 'WSFmanualrefer';
    case PROPENSIO_PAID_OUT = 'WSFpaidout';
    case PROPENSIO_CANCELLED_POST_PAYO = 'WSFcancelledpostpayo';
    case PROPENSIO_WITHDRAWN_POST_PAYO = 'WSFwithdrawnpostpayo';
    case PROPENSIO_QUOTE_EXPIRED = 'WSFquoteexpired';
    case PROPENSIO_APPLICATION_EXPIRED = 'WSFappexpired';

}

<?php

namespace Mralston\Payment\Enums;

enum PaymentStage: string
{
    case PREQUAL = 'prequal';
    case APPLY = 'apply';
    case CANCEL = 'cancel';
    case STATUS_POLL = 'status_poll';
    case SAT_NOTE_UPLOAD = 'sat_note_upload';
}
<?php

namespace Mralston\Payment\Enums;

enum ApiAction: string
{
    case PROPENSIO_CANCEL_APPLICATION = 'setApplicationToNpw';
}
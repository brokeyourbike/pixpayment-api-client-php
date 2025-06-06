<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\PixPayment\Enums;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
enum TransactionStatusEnum: string
{
    case CREATED_LOW = 'created';
    case IN_PROGRESS = 'InProgress';
    case PENDING = 'Pending';
    case SUCCESSFUL = 'Successful';
    case FAILED = 'Failed';
    case FAILED_LOW = 'failed';
    case ABORTED = 'Aborted';
    case CANCELLED = 'Cancelled';
}

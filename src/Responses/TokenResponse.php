<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\PixPayment\Responses;

use BrokeYourBike\DataTransferObject\JsonResponse;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
class TokenResponse extends JsonResponse
{
    public ?string $error;
    public ?string $error_description;
    public ?string $access_token;
    public ?int $expires_in;
}

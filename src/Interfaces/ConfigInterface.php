<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\PixPayment\Interfaces;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
interface ConfigInterface
{
    public function getUrl(): string;
    public function getClientId(): string;
    public function getClientSecret(): string;
    public function getAccountId(): string;
    public function getPartnerCode(): string;
}

<?php

// Copyright (C) 2024 Ivan Stasiuk <ivan@stasi.uk>.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.

namespace BrokeYourBike\PixPayment\Interfaces;

/**
 * @author Ivan Stasiuk <ivan@stasi.uk>
 */
interface TransactionInterface
{
    public function getCurrency(): string;
    public function getAmount(): float;

    public function getSenderCountry(): string;

    public function getRecipientFirstName(): string;
    public function getRecipientLastName(): string;
    public function getRecipientPhone(): string;
    public function getRecipientProvider(): string;
    public function getRecipientCountry(): string;
}

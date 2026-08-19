<?php

namespace App\DTOs;

class POSInvoiceItemDTO
{
    public function __construct(
        public readonly int $itemId,
        public readonly float $quantity,
        public readonly float $unitPrice,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            itemId: (int)$data['item_id'],
            quantity: (float)$data['quantity'],
            unitPrice: (float)$data['unit_price'],
        );
    }

    public function toArray(): array
    {
        return [
            'item_id' => $this->itemId,
            'quantity' => (string)$this->quantity,
            'unit_price' => (string)$this->unitPrice,
        ];
    }
}

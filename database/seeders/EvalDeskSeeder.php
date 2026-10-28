<?php

namespace Database\Seeders;

use App\Models\EvalCase;
use App\Models\FaqChunk;
use Illuminate\Database\Seeder;

class EvalDeskSeeder extends Seeder
{
    public function run(): void
    {
        $chunks = [
            ['id' => 'shipping-window', 'title' => 'Shipping window', 'content' => 'Orders leave our warehouse within 1–2 business days. A tracking email arrives after the carrier scans the parcel.'],
            ['id' => 'return-policy', 'title' => 'Return policy', 'content' => 'Unused items can be returned within 30 days of delivery in their original packaging. Start a return by contacting support with your order number.'],
            ['id' => 'refund-timing', 'title' => 'Refund timing', 'content' => 'After a returned item is received and inspected, refunds are sent to the original payment method within 5–7 business days.'],
            ['id' => 'cancel-order', 'title' => 'Cancel an order', 'content' => 'An order can be cancelled before warehouse processing begins. Email support with your order number; shipped orders cannot be cancelled.'],
            ['id' => 'track-parcel', 'title' => 'Track a parcel', 'content' => 'Use the tracking link in your shipment confirmation email to see the latest carrier updates and estimated delivery date.'],
            ['id' => 'payment-methods', 'title' => 'Accepted payment methods', 'content' => 'Harbor Desk accepts Visa, Mastercard, American Express, and PayPal. Payment is collected securely at checkout.'],
            ['id' => 'damaged-delivery', 'title' => 'Damaged delivery', 'content' => 'If an item arrives damaged, photograph the item and packaging and contact support within 48 hours. We will arrange a replacement or refund.'],
            ['id' => 'contact-support', 'title' => 'Contact support', 'content' => 'Email support@harbordesk.example. Our support team replies Monday through Friday, usually within one business day.'],
        ];

        foreach ($chunks as $chunk) {
            FaqChunk::query()->updateOrCreate(['id' => $chunk['id']], $chunk);
        }

        $cases = [
            ['question' => 'How soon does my order ship after checkout?', 'relevant_ids' => ['shipping-window']],
            ['question' => 'Can I return an item and how long does the refund take?', 'relevant_ids' => ['return-policy', 'refund-timing']],
            ['question' => 'Where can I find the tracking link for my parcel?', 'relevant_ids' => ['track-parcel']],
            ['question' => 'Can I cancel an order before it leaves the warehouse?', 'relevant_ids' => ['cancel-order']],
            ['question' => 'My delivery arrived damaged. How do I get a replacement or refund?', 'relevant_ids' => ['damaged-delivery', 'contact-support']],
        ];

        foreach ($cases as $case) {
            EvalCase::query()->updateOrCreate(['question' => $case['question']], $case);
        }
    }
}

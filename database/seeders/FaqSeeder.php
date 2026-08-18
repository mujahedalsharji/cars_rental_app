<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I rent a car?',
                'answer' => 'Browse our fleet, choose a car that fits your needs, and click "Inquire on WhatsApp". Our team will confirm availability and guide you through the process.',
                'category' => 'Rental Process',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'question' => 'What documents do I need to rent a car?',
                'answer' => 'You will need a valid driving license, a national ID or passport, and a credit or debit card for the security deposit.',
                'category' => 'Rental Process',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'question' => 'What is the minimum rental period?',
                'answer' => 'Our minimum rental period is one day (24 hours). We also offer weekly and monthly rates for longer-term rentals.',
                'category' => 'Rental Process',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'question' => 'How is pricing determined?',
                'answer' => 'Pricing depends on the car model, rental period, and any optional add-ons. Contact us on WhatsApp for a personalized quote.',
                'category' => 'Pricing',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'question' => 'Is there a security deposit?',
                'answer' => 'Yes, a refundable security deposit is required at the time of rental. The amount varies based on the vehicle category.',
                'category' => 'Pricing',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'question' => 'Does the rental price include insurance?',
                'answer' => 'All our vehicles come with basic third-party insurance. Comprehensive coverage upgrades are available — please ask our team for details.',
                'category' => 'Insurance',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'question' => 'What happens if the car is involved in an accident?',
                'answer' => 'Contact our team immediately. Do not move the vehicle if it is unsafe to do so. We will guide you through the claims process step by step.',
                'category' => 'Insurance',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'question' => 'Can I return the car early?',
                'answer' => 'Yes. Early returns are accepted. Refunds for unused days depend on the rental agreement — please check with our team when booking.',
                'category' => 'Rental Process',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'question' => 'Is fuel included in the price?',
                'answer' => 'No, fuel is not included. Cars are provided with a full tank and must be returned with a full tank, or a fuel fee will apply.',
                'category' => 'Pricing',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'question' => 'Can I take the car outside the country?',
                'answer' => 'Cross-border travel requires prior approval. Please notify us during booking so we can arrange the necessary permits and insurance coverage.',
                'category' => 'Rental Process',
                'is_active' => true,
                'sort_order' => 10,
            ],
        ];

        foreach ($faqs as $data) {
            Faq::firstOrCreate(['question' => $data['question']], $data);
        }
    }
}

<?php

namespace Database\Seeders\Support;

class MarketplaceCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function categories(): array
    {
        return [
            [
                'name' => 'Beauty & Grooming',
                'slug' => 'beauty-grooming',
                'icon_name' => 'brush_2',
                'image_url' => 'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Signature Silk Press', 'base_price_amount' => 85000, 'duration_minutes' => 120, 'is_featured' => true],
                    ['name' => 'Bridal Makeup Session', 'base_price_amount' => 120000, 'duration_minutes' => 90, 'is_featured' => true],
                    ['name' => 'Executive Barber Cut', 'base_price_amount' => 30000, 'duration_minutes' => 45, 'is_featured' => true],
                    ['name' => 'Medium Knotless Braids', 'base_price_amount' => 160000, 'duration_minutes' => 240, 'is_featured' => true],
                    ['name' => 'Luxury Gel Manicure', 'base_price_amount' => 35000, 'duration_minutes' => 60],
                    ['name' => 'Therapeutic Body Massage', 'base_price_amount' => 95000, 'duration_minutes' => 90],
                    ['name' => 'Children Hair Braiding', 'base_price_amount' => 55000, 'duration_minutes' => 110],
                    ['name' => 'Classic Facial Grooming', 'base_price_amount' => 45000, 'duration_minutes' => 50],
                ],
            ],
            [
                'name' => 'Home Cleaning',
                'slug' => 'home-cleaning',
                'icon_name' => 'broom',
                'image_url' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Deep Home Cleaning', 'base_price_amount' => 120000, 'duration_minutes' => 180, 'is_featured' => true],
                    ['name' => 'Move In Cleaning', 'base_price_amount' => 150000, 'duration_minutes' => 240],
                    ['name' => 'Post Construction Cleaning', 'base_price_amount' => 220000, 'duration_minutes' => 300],
                    ['name' => 'Sofa Shampoo Cleaning', 'base_price_amount' => 80000, 'duration_minutes' => 90],
                ],
            ],
            [
                'name' => 'Laundry & Dry Cleaning',
                'slug' => 'laundry-dry-cleaning',
                'icon_name' => 'washing_machine',
                'image_url' => 'https://images.unsplash.com/photo-1604335399105-a0c585fd81a1?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Wash and Fold Laundry', 'base_price_amount' => 25000, 'duration_minutes' => 45],
                    ['name' => 'Express Dry Cleaning', 'base_price_amount' => 30000, 'duration_minutes' => 45],
                    ['name' => 'Curtain Cleaning', 'base_price_amount' => 70000, 'duration_minutes' => 120],
                    ['name' => 'Duvet Cleaning', 'base_price_amount' => 40000, 'duration_minutes' => 60],
                ],
            ],
            [
                'name' => 'Plumbing',
                'slug' => 'plumbing',
                'icon_name' => 'setting_2',
                'image_url' => 'https://images.unsplash.com/photo-1585704032915-c3400ca199e7?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Leak Repair', 'base_price_amount' => 45000, 'duration_minutes' => 60, 'is_featured' => true],
                    ['name' => 'Drain Unblocking', 'base_price_amount' => 65000, 'duration_minutes' => 90],
                    ['name' => 'Toilet Installation', 'base_price_amount' => 120000, 'duration_minutes' => 120],
                    ['name' => 'Water Heater Plumbing', 'base_price_amount' => 150000, 'duration_minutes' => 150],
                ],
            ],
            [
                'name' => 'Electrical',
                'slug' => 'electrical',
                'icon_name' => 'flash',
                'image_url' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Socket and Switch Repair', 'base_price_amount' => 35000, 'duration_minutes' => 45],
                    ['name' => 'House Rewiring', 'base_price_amount' => 300000, 'duration_minutes' => 360, 'is_featured' => true],
                    ['name' => 'Security Lighting Installation', 'base_price_amount' => 90000, 'duration_minutes' => 90],
                    ['name' => 'Generator Changeover Setup', 'base_price_amount' => 180000, 'duration_minutes' => 150],
                ],
            ],
            [
                'name' => 'Appliance Repair',
                'slug' => 'appliance-repair',
                'icon_name' => 'setting_4',
                'image_url' => 'https://images.unsplash.com/photo-1581093458791-9f3c3900df4b?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Fridge Repair', 'base_price_amount' => 120000, 'duration_minutes' => 120],
                    ['name' => 'Washing Machine Repair', 'base_price_amount' => 150000, 'duration_minutes' => 150],
                    ['name' => 'Cooker Repair', 'base_price_amount' => 100000, 'duration_minutes' => 120],
                    ['name' => 'Microwave Repair', 'base_price_amount' => 80000, 'duration_minutes' => 90],
                ],
            ],
            [
                'name' => 'Air Conditioning & Refrigeration',
                'slug' => 'air-conditioning-refrigeration',
                'icon_name' => 'wind_2',
                'image_url' => 'https://images.unsplash.com/photo-1631545806526-39f1f658b0a6?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'AC Installation', 'base_price_amount' => 200000, 'duration_minutes' => 180],
                    ['name' => 'AC Gas Refill', 'base_price_amount' => 150000, 'duration_minutes' => 90],
                    ['name' => 'Cold Room Maintenance', 'base_price_amount' => 250000, 'duration_minutes' => 180],
                    ['name' => 'Refrigerator Servicing', 'base_price_amount' => 120000, 'duration_minutes' => 120],
                ],
            ],
            [
                'name' => 'Carpentry & Joinery',
                'slug' => 'carpentry-joinery',
                'icon_name' => 'hammer',
                'image_url' => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Door Hanging', 'base_price_amount' => 90000, 'duration_minutes' => 120],
                    ['name' => 'Wardrobe Installation', 'base_price_amount' => 180000, 'duration_minutes' => 180],
                    ['name' => 'Kitchen Cabinet Repair', 'base_price_amount' => 120000, 'duration_minutes' => 120],
                    ['name' => 'Custom Shelving', 'base_price_amount' => 140000, 'duration_minutes' => 150],
                ],
            ],
            [
                'name' => 'Painting & Decoration',
                'slug' => 'painting-decoration',
                'icon_name' => 'brush_4',
                'image_url' => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Interior Wall Painting', 'base_price_amount' => 250000, 'duration_minutes' => 300],
                    ['name' => 'Exterior Painting', 'base_price_amount' => 350000, 'duration_minutes' => 360],
                    ['name' => 'Wallpaper Installation', 'base_price_amount' => 180000, 'duration_minutes' => 180],
                    ['name' => 'Skimming and Surface Preparation', 'base_price_amount' => 220000, 'duration_minutes' => 240],
                ],
            ],
            [
                'name' => 'Masonry & Building',
                'slug' => 'masonry-building',
                'icon_name' => 'building_4',
                'image_url' => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Wall Construction', 'base_price_amount' => 400000, 'duration_minutes' => 480],
                    ['name' => 'Floor Screeding', 'base_price_amount' => 280000, 'duration_minutes' => 300],
                    ['name' => 'Paving Installation', 'base_price_amount' => 320000, 'duration_minutes' => 360],
                    ['name' => 'Concrete Repairs', 'base_price_amount' => 250000, 'duration_minutes' => 240],
                ],
            ],
            [
                'name' => 'Roofing & Waterproofing',
                'slug' => 'roofing-waterproofing',
                'icon_name' => 'house',
                'image_url' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Roof Leak Repair', 'base_price_amount' => 180000, 'duration_minutes' => 180],
                    ['name' => 'Ceiling Board Repair', 'base_price_amount' => 150000, 'duration_minutes' => 150],
                    ['name' => 'Gutter Installation', 'base_price_amount' => 220000, 'duration_minutes' => 180],
                    ['name' => 'Waterproofing Treatment', 'base_price_amount' => 260000, 'duration_minutes' => 240],
                ],
            ],
            [
                'name' => 'Welding & Metal Fabrication',
                'slug' => 'welding-metal-fabrication',
                'icon_name' => 'weight',
                'image_url' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Window Grills Fabrication', 'base_price_amount' => 220000, 'duration_minutes' => 240],
                    ['name' => 'Gate Repair', 'base_price_amount' => 150000, 'duration_minutes' => 150],
                    ['name' => 'Metal Door Fabrication', 'base_price_amount' => 350000, 'duration_minutes' => 300],
                    ['name' => 'Stainless Railing Installation', 'base_price_amount' => 260000, 'duration_minutes' => 240],
                ],
            ],
            [
                'name' => 'Movers & Relocation',
                'slug' => 'movers-relocation',
                'icon_name' => 'truck_fast',
                'image_url' => 'https://images.unsplash.com/photo-1600518464441-9154a4dea21b?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Studio House Moving', 'base_price_amount' => 180000, 'duration_minutes' => 240, 'is_featured' => true],
                    ['name' => 'Office Relocation', 'base_price_amount' => 450000, 'duration_minutes' => 360],
                    ['name' => 'Furniture Pickup and Delivery', 'base_price_amount' => 120000, 'duration_minutes' => 150],
                    ['name' => 'Packing Assistance', 'base_price_amount' => 100000, 'duration_minutes' => 120],
                ],
            ],
            [
                'name' => 'Mechanics & Auto Repair',
                'slug' => 'mechanics-auto-repair',
                'icon_name' => 'car',
                'image_url' => 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Car Engine Diagnostics', 'base_price_amount' => 85000, 'duration_minutes' => 60],
                    ['name' => 'Brake Pad Replacement', 'base_price_amount' => 140000, 'duration_minutes' => 120],
                    ['name' => 'Minor Car Service', 'base_price_amount' => 180000, 'duration_minutes' => 150],
                    ['name' => 'Clutch Repair', 'base_price_amount' => 300000, 'duration_minutes' => 240],
                ],
            ],
            [
                'name' => 'Tyre & Battery Rescue',
                'slug' => 'tyre-battery-rescue',
                'icon_name' => 'driver_refresh',
                'image_url' => 'https://images.unsplash.com/photo-1607860108855-64acf2078ed9?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Flat Tyre Rescue', 'base_price_amount' => 45000, 'duration_minutes' => 45],
                    ['name' => 'Battery Jump Start', 'base_price_amount' => 35000, 'duration_minutes' => 30],
                    ['name' => 'Battery Replacement', 'base_price_amount' => 90000, 'duration_minutes' => 45],
                    ['name' => 'Wheel Alignment Booking', 'base_price_amount' => 70000, 'duration_minutes' => 60],
                ],
            ],
            [
                'name' => 'Car Wash & Detailing',
                'slug' => 'car-wash-detailing',
                'icon_name' => 'car',
                'image_url' => 'https://images.unsplash.com/photo-1517524206127-48bbd363f3d7?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Exterior Car Wash', 'base_price_amount' => 25000, 'duration_minutes' => 30],
                    ['name' => 'Full Interior Detailing', 'base_price_amount' => 120000, 'duration_minutes' => 120],
                    ['name' => 'Engine Bay Cleaning', 'base_price_amount' => 60000, 'duration_minutes' => 60],
                    ['name' => 'Seat Shampoo', 'base_price_amount' => 80000, 'duration_minutes' => 90],
                ],
            ],
            [
                'name' => 'IT Support',
                'slug' => 'it-support',
                'icon_name' => 'monitor_mobbile',
                'image_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Onsite Computer Troubleshooting', 'base_price_amount' => 70000, 'duration_minutes' => 60, 'is_featured' => true],
                    ['name' => 'Printer Setup', 'base_price_amount' => 50000, 'duration_minutes' => 45],
                    ['name' => 'Software Installation', 'base_price_amount' => 40000, 'duration_minutes' => 45],
                    ['name' => 'Email Setup Assistance', 'base_price_amount' => 35000, 'duration_minutes' => 30],
                ],
            ],
            [
                'name' => 'Phone & Laptop Repair',
                'slug' => 'phone-laptop-repair',
                'icon_name' => 'mobile',
                'image_url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Phone Screen Replacement', 'base_price_amount' => 120000, 'duration_minutes' => 90],
                    ['name' => 'Laptop Keyboard Repair', 'base_price_amount' => 100000, 'duration_minutes' => 90],
                    ['name' => 'Data Recovery', 'base_price_amount' => 160000, 'duration_minutes' => 150],
                    ['name' => 'Charging Port Repair', 'base_price_amount' => 85000, 'duration_minutes' => 60],
                ],
            ],
            [
                'name' => 'CCTV & Security Systems',
                'slug' => 'cctv-security-systems',
                'icon_name' => 'security_safe',
                'image_url' => 'https://images.unsplash.com/photo-1558002038-1055e2e28ed1?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'CCTV Camera Installation', 'base_price_amount' => 260000, 'duration_minutes' => 180],
                    ['name' => 'Access Control Setup', 'base_price_amount' => 300000, 'duration_minutes' => 180],
                    ['name' => 'Electric Fence Repair', 'base_price_amount' => 180000, 'duration_minutes' => 120],
                    ['name' => 'Alarm System Installation', 'base_price_amount' => 220000, 'duration_minutes' => 150],
                ],
            ],
            [
                'name' => 'Internet & Networking',
                'slug' => 'internet-networking',
                'icon_name' => 'wifi_square',
                'image_url' => 'https://images.unsplash.com/photo-1520869562399-e772f042f422?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'WiFi Router Setup', 'base_price_amount' => 45000, 'duration_minutes' => 40],
                    ['name' => 'Network Cabling', 'base_price_amount' => 150000, 'duration_minutes' => 150],
                    ['name' => 'Office Network Troubleshooting', 'base_price_amount' => 120000, 'duration_minutes' => 90],
                    ['name' => 'Starlink Installation', 'base_price_amount' => 180000, 'duration_minutes' => 120],
                ],
            ],
            [
                'name' => 'Gardening & Landscaping',
                'slug' => 'gardening-landscaping',
                'icon_name' => 'tree',
                'image_url' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Compound Slashing', 'base_price_amount' => 60000, 'duration_minutes' => 90],
                    ['name' => 'Hedge Trimming', 'base_price_amount' => 75000, 'duration_minutes' => 90],
                    ['name' => 'Lawn Maintenance', 'base_price_amount' => 95000, 'duration_minutes' => 120],
                    ['name' => 'Landscape Design Visit', 'base_price_amount' => 120000, 'duration_minutes' => 90],
                ],
            ],
            [
                'name' => 'Pest Control & Fumigation',
                'slug' => 'pest-control-fumigation',
                'icon_name' => 'danger',
                'image_url' => 'https://images.unsplash.com/photo-1581578731582-52f8f681b2e0?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Cockroach Fumigation', 'base_price_amount' => 95000, 'duration_minutes' => 90],
                    ['name' => 'Bedbug Treatment', 'base_price_amount' => 140000, 'duration_minutes' => 120],
                    ['name' => 'Termite Control', 'base_price_amount' => 180000, 'duration_minutes' => 150],
                    ['name' => 'Rat Control Service', 'base_price_amount' => 100000, 'duration_minutes' => 90],
                ],
            ],
            [
                'name' => 'Waste Collection & Toilet Emptying',
                'slug' => 'waste-collection-toilet-emptying',
                'icon_name' => 'trash',
                'image_url' => 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Septic Tank Emptying', 'base_price_amount' => 220000, 'duration_minutes' => 180],
                    ['name' => 'Toilet Unblocking', 'base_price_amount' => 70000, 'duration_minutes' => 60],
                    ['name' => 'Garbage Collection Pickup', 'base_price_amount' => 45000, 'duration_minutes' => 45],
                    ['name' => 'Drainage Pit Emptying', 'base_price_amount' => 180000, 'duration_minutes' => 150],
                ],
            ],
            [
                'name' => 'Water Delivery & Tank Cleaning',
                'slug' => 'water-delivery-tank-cleaning',
                'icon_name' => 'drop',
                'image_url' => 'https://images.unsplash.com/photo-1502741338009-cac2772e18bc?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Water Tank Cleaning', 'base_price_amount' => 130000, 'duration_minutes' => 120],
                    ['name' => 'Emergency Water Delivery', 'base_price_amount' => 80000, 'duration_minutes' => 60],
                    ['name' => 'Borehole Pump Check', 'base_price_amount' => 95000, 'duration_minutes' => 75],
                    ['name' => 'Water Tank Installation', 'base_price_amount' => 240000, 'duration_minutes' => 180],
                ],
            ],
            [
                'name' => 'Events & Catering',
                'slug' => 'events-catering',
                'icon_name' => 'cup',
                'image_url' => 'https://images.unsplash.com/photo-1555244162-803834f70033?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Small Event Catering', 'base_price_amount' => 250000, 'duration_minutes' => 180],
                    ['name' => 'Corporate Lunch Delivery', 'base_price_amount' => 180000, 'duration_minutes' => 90],
                    ['name' => 'Event Decor Setup', 'base_price_amount' => 300000, 'duration_minutes' => 240],
                    ['name' => 'Event MC and Coordination', 'base_price_amount' => 220000, 'duration_minutes' => 240],
                ],
            ],
            [
                'name' => 'Photography & Videography',
                'slug' => 'photography-videography',
                'icon_name' => 'camera',
                'image_url' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Event Photography', 'base_price_amount' => 280000, 'duration_minutes' => 240],
                    ['name' => 'Corporate Headshots', 'base_price_amount' => 180000, 'duration_minutes' => 120],
                    ['name' => 'Product Photography', 'base_price_amount' => 220000, 'duration_minutes' => 150],
                    ['name' => 'Event Videography', 'base_price_amount' => 350000, 'duration_minutes' => 240],
                ],
            ],
            [
                'name' => 'Printing & Branding',
                'slug' => 'printing-branding',
                'icon_name' => 'document',
                'image_url' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Business Card Printing', 'base_price_amount' => 35000, 'duration_minutes' => 30],
                    ['name' => 'T Shirt Branding', 'base_price_amount' => 60000, 'duration_minutes' => 60],
                    ['name' => 'Sticker Printing', 'base_price_amount' => 45000, 'duration_minutes' => 45],
                    ['name' => 'Banner Design and Print', 'base_price_amount' => 90000, 'duration_minutes' => 60],
                ],
            ],
            [
                'name' => 'Childcare & Babysitting',
                'slug' => 'childcare-babysitting',
                'icon_name' => 'profile_2user',
                'image_url' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Daytime Babysitting', 'base_price_amount' => 70000, 'duration_minutes' => 240],
                    ['name' => 'Evening Babysitting', 'base_price_amount' => 90000, 'duration_minutes' => 300],
                    ['name' => 'School Pickup Support', 'base_price_amount' => 65000, 'duration_minutes' => 120],
                    ['name' => 'Newborn Care Assistance', 'base_price_amount' => 110000, 'duration_minutes' => 240],
                ],
            ],
            [
                'name' => 'Elderly Care & Home Nursing',
                'slug' => 'elderly-care-home-nursing',
                'icon_name' => 'heart',
                'image_url' => 'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Home Nursing Visit', 'base_price_amount' => 120000, 'duration_minutes' => 120],
                    ['name' => 'Elderly Companionship', 'base_price_amount' => 85000, 'duration_minutes' => 180],
                    ['name' => 'Medication Reminder Support', 'base_price_amount' => 70000, 'duration_minutes' => 90],
                    ['name' => 'Post Hospital Care', 'base_price_amount' => 150000, 'duration_minutes' => 180],
                ],
            ],
            [
                'name' => 'Tutoring & Coaching',
                'slug' => 'tutoring-coaching',
                'icon_name' => 'book',
                'image_url' => 'https://images.unsplash.com/photo-1513258496099-48168024aec0?auto=format&fit=crop&w=900&q=80',
                'services' => [
                    ['name' => 'Mathematics Tutoring', 'base_price_amount' => 50000, 'duration_minutes' => 90],
                    ['name' => 'English Tutoring', 'base_price_amount' => 45000, 'duration_minutes' => 90],
                    ['name' => 'Coding Lessons', 'base_price_amount' => 80000, 'duration_minutes' => 120],
                    ['name' => 'Exam Revision Coaching', 'base_price_amount' => 60000, 'duration_minutes' => 120],
                ],
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ContactUsController extends Controller
{
    public function index(): Response
    {
        $address = 'No.14/S Waragashinna, Akurana 20850';
        $encodedAddress = rawurlencode($address);

        return Inertia::render('Frontend/contactus/index', [
            'page' => [
                'title' => 'Contact Us',
                'eyebrow' => 'Get in touch',
                'subtitle' => 'We would be pleased to assist you with product inquiries, order support, and all general questions related to our store.',
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Contact Us', 'href' => null],
                ],
            ],
            'contact' => [
                'address' => $address,
                'phones' => [
                    '077 203 0597',
                ],
                'email' => 'dezestore@gmail.com',
                'map_title' => 'We are located at',
                'map_url' => "https://www.google.com/maps/search/?api=1&query={$encodedAddress}",
                'map_embed_url' => "https://maps.google.com/maps?q={$encodedAddress}&z=16&output=embed",
            ],
        ]);
    }
}

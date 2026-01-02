<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    @livewire('layouts.navbar')

    <div class="min-h-screen">
        @component('guest.home.components.about-us')

        @endcomponent

        <section class="w-full bg-primary px-24 py-16 text-white">
            <div class="flex gap-10 items-start">
                <div class="flex-1 space-y-6" data-aos="fade-up">
                    <div class="space-y-2">
                        <h2 class="text-4xl/tight font-semibold">Kontak Kami</h2>
                        <p class="text-sm/relaxed font-light">
                            Jika Anda ingin bertanya seputar layanan, jadwal, atau informasi lainnya, silakan hubungi
                            kami
                            melalui kanal berikut.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1">
                            <p class="text-sm font-semibold">Alamat</p>
                            <p class="text-sm/relaxed font-light">
                                km 10 no.32A, Jl. Perintis Kemerdekaan, Tamalanrea Jaya, Kec. Tamalanrea, Kota Makassar,
                                Sulawesi Selatan 90245
                            </p>
                        </div>

                        <div class="space-y-1">
                            <p class="text-sm font-semibold">Instagram</p>
                            <a class="text-sm font-light underline" href="https://www.instagram.com/monopics.mks/"
                                target="_blank" rel="noopener noreferrer">
                                @monopics.mks
                            </a>
                        </div>

                        <div class="space-y-1">
                            <p class="text-sm font-semibold">Maps</p>
                            <a class="text-sm font-light underline" target="_blank" rel="noopener noreferrer"
                                href="https://www.google.com/maps/search/?api=1&query=km%2010%20no.32A%2C%20Jl.%20Perintis%20Kemerdekaan%2C%20Tamalanrea%20Jaya%2C%20Makassar">
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex-1" data-aos="fade-up">
                    <div class="rounded-2xl overflow-hidden bg-white">
                        <div class="relative w-full" style="padding-top: 56.25%;">
                            <!-- <iframe width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe> -->
                            <iframe class="absolute inset-0 w-full h-full" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                 src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4713.6729435143225!2d119.48616617498222!3d-5.141333494835864!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dbee3001f5f4dd5%3A0x798e25b56d65c128!2sMonopic%20Studio%20Makassar!5e1!3m2!1sid!2sid!4v1767325119371!5m2!1sid!2sid">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>



    @livewire('layouts.footter')

</div>

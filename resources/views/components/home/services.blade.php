<section class="bg-primary ">
    <div class="w-full max-w-7xl mx-auto min-h-screen p-24 space-y-14 text-white">
        <div class="space-y-5 flex-1 pt-8 text-center" data-aos="fade-up">
            <h1 class="text-6xl/tight font-semibold">Paket Photoshoot Unggulan Kami</h1>
            <p class="text-md/loose font-light ">
                Pilih paket photoshoot terbaik untuk momen spesialmu
            </p>
        </div>
        <div class="flex-1 " data-aos="fade-up">
            <img src="{{ asset('images/xmas.png') }}" alt="" srcset="">
        </div>
        <div class="py-14 ">
            @foreach ([1, 2, 3] as $service)
                <div
                    class="block will-change-transform transform transition-transform duration-300 ease-in-out hover:scale-105">

                    <div class="h-screen flex {{ $service % 2 == 0 ? 'flex-row-reverse' : '' }} gap-10 pb-24 cursor-pointer "
                        data-aos="fade-up">
                        <div class="flex-1 h-full">
                            <img src="{{ asset('images/gallery-' . $service . '.jpg') }}" alt=""
                                class="h-full object-cover">
                        </div>
                        <div class="flex-1 overflow-hidden space-y-5">
                            <h1 class="text-5xl/relaxed font-semibold uppercase">Paket Lorem, ipsum dolor.
                                {{ $service }}</h1>
                            <h1 class="text-5xl/relaxed font-semibold ">Rp. {{ number_format($service * 100000) }}</h1>
                            <p class="text-xl/relaxed font-light text-justify">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Architecto est commodi quod,
                                rem
                                vero soluta obcaecati aliquid voluptatem veritatis consectetur recusandae eum facere
                                eveniet
                                voluptas! Molestias pariatur labore optio quod!
                            </p>
                            <div class="space-y-5 text-xl/relaxed font-light text-justify">
                                <h1 class="text-md ">Fasilitas</h1>
                                <ul class="grid grid-cols-2 gap-5">
                                    <li class=" flex  items-center gap-5">
                                        <span class="h-3 aspect-square bg-white"></span>
                                        <p class="text-md/tight">Max Person: {{ floor($service * 5) }}</p>
                                    </li>
                                    <li class=" flex  items-center gap-5">
                                        <span class="h-3 aspect-square bg-white"></span>
                                        <p class="text-md/tight">Bisa Pilih Kostum</p>
                                    </li>
                                    <li class=" flex  items-center gap-5">
                                        <span class="h-3 aspect-square bg-white"></span>
                                        <p class="text-md/tight">Edit di tempat</p>
                                    </li>
                                    <li class=" flex  items-center gap-5">
                                        <span class="h-3 aspect-square bg-white"></span>
                                        <p class="text-md/tight">Lama: {{ $service * 6 }} Menit</p>
                                    </li>
                                </ul>
                            </div>


                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</section>

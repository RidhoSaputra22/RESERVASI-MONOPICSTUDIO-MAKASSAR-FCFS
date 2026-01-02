
<div>
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
                @foreach ($paket as $key => $item)
                    <a href="{{ route('guest.booking', $item->slug) }}"
                        class="block will-change-transform transform transition-transform duration-300 ease-in-out hover:scale-105">

                        <div class="h-screen flex {{ ($key + 1) % 2 == 0 ? 'flex-row-reverse' : '' }} gap-10 pb-24 cursor-pointer "
                            data-aos="fade-up">
                            <div class="flex-1 h-full">
                                <img src="{{ Storage::url($item->photo ?? 'packages/package_placeholder.jpg') }}" alt=""
                                    class="h-full object-cover">
                            </div>
                            <div class="flex-1 overflow-hidden space-y-5">
                                <h1 class="text-5xl/relaxed font-semibold uppercase">
                                    {{ $item->name }}</h1>
                                <h1 class="text-5xl/relaxed font-semibold ">Rp. {{ number_format($item->price) }}</h1>
                                <p class="text-xl/relaxed font-light text-justify">
                                    {{ $item->description }}
                                </p>
                                <div class="space-y-5 text-xl/relaxed font-light text-justify">
                                      <div class=" flex  items-center gap-5">
                                            @component('components.icon.clock')

                                            @endcomponent
                                            <p class="text-md/tight">{{ $item->duration_minutes }} Menit</p>
                                        </div>
                                </div>


                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

    </section>

</div>

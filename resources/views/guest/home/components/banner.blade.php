
<div>
    <section class="relative ">
        <nav class="h-18 my-4 absolute top-0  w-full z-10 font-light">
            <div class="max-w-7xl mx-auto flex h-full bg-white rounded-full items-center pl-9 pr-7 ">
                <div class="flex-1 flex gap-10">
                    <a href="{{ route('welcome') }}">Beranda</a>
                    <a href="{{ route('about-us') }} ">About Us</a>
                    <a href=" {{ route('paket') }}">Paket</a>
                </div>
                <div class="">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="h-13 ">
                </div>
                <div class="flex-1 flex justify-end gap-10">
                    <a href="{{ route('paket') }}" class="bg-primary text-white px-4 py-3 rounded-full ">Reservasi Sekarang</a>

                </div>
            </div>

        </nav>
        <!-- Swiper -->
        <div class="swiper bannerSwiper h-150">
            <div class="swiper-wrapper *:bg-primary">
                <img class="swiper-slide h-full w-full object-cover object-center" src="{{ asset('images/banner-1.png') }}" alt="" >
                <img class="swiper-slide h-full w-full object-cover object-center" src="{{ asset('images/banner-paket-2.png') }}" alt="" >
                <img class="swiper-slide h-full w-full object-cover object-center" src="{{ asset('images/banner-paket-3.png') }}" alt="" >


            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    @push('scripts')
        <script>
            const swiper = new Swiper(".bannerSwiper", {
                slidesPerView: 1,
                centeredSlides: true,
                loop: true,
                speed: 400,
                // spaceBetween: 30,

                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },



                pagination: {
                    el: ".swiper-pagination",

                },
            });
        </script>
    @endpush

</div>

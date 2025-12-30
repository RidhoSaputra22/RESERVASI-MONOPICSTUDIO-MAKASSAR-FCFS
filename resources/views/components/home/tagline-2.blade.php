<section class="w-full  gap-10 p-24 space-y-14">
    <div class="  text-primary pt-8" data-aos="fade-up">
        <div class="">
            <h1 class="text-6xl/tight font-semibold">Lebih dari 100 orang telah mempercayakan kisahnnya kepada
                kami, Jadilah salah satunya</h1>
        </div>
    </div>
    <div class="swiper gallerySwiper h-96 w-full" data-aos="fade-up">
        <div class="swiper-wrapper ">
            @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10] as $i)
                <div class="relative swiper-slide h-96 w-full
                ">
                    <img src="{{ asset('images/gallery-' . $i . '.jpg') }}" alt=""
                        class="h-full w-full object-cover object-center ">
                </div>
            @endforeach


        </div>
        <div class="swiper-pagination"></div>
    </div>

</section>

@push('scripts')
    <script>
        const gallerySwiper = new Swiper(".gallerySwiper", {
            slidesPerView: 4,
            spaceBetween: 16,
            loop: true,
            speed: 400,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
        });
    </script>
@endpush

<section class="h-screen w-full max-w-7xl mx-auto min-h-screen p-24 space-y-14 text-primary">
    <div class="space-y-5 flex-1 pt-8 text-center" data-aos="fade-up">
        <h1 class="text-6xl/tight font-semibold">Fequency Asked Question</h1>
        <p class="text-md/loose font-light ">Berikut pertanyaan yang paling sering ditanyai</p>

    </div>
    <div class="w-full divide-y divide-outline text-on-surface dark:divide-outline-dark dark:text-on-surface-dark"
        data-aos="fade-up">
        <div x-data="{ isExpanded: false }">
            <button id="controlsAccordionItemOne" type="button"
                class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded"
                x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                    'text-on-surface dark:text-on-surface-dark font-medium'"
                x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                Bagaimana cara melakukan pemesanan photo shoot di Monopic Studio?
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                    stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                    x-bind:class="isExpanded ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                aria-labelledby="controlsAccordionItemOne" x-collapse>
                <div class="pb-4 text-sm sm:text-base text-pretty ">
                    Pemesanan dapat dilakukan secara online melalui website kami dengan memilih jenis layanan, paket
                    photo studio, serta jadwal yang tersedia. Setelah pemesanan dilakukan, Anda akan menerima konfirmasi
                    melalui notifikasi otomatis untuk memastikan detail jadwal dan layanan yang dipilih.
                </div>
            </div>
        </div>
        <div x-data="{ isExpanded: false }">
            <button id="controlsAccordionItemOne" type="button"
                class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded"
                x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                    'text-on-surface dark:text-on-surface-dark font-medium'"
                x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                Apakah jadwal photo shoot dapat diubah atau dibatalkan?
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                    stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                    x-bind:class="isExpanded ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                aria-labelledby="controlsAccordionItemOne" x-collapse>
                <div class="pb-4 text-sm sm:text-base text-pretty ">
                    Ya, perubahan atau pembatalan jadwal dapat dilakukan sesuai dengan ketentuan yang berlaku. Anda
                    dapat mengajukan permintaan melalui sistem atau menghubungi tim kami, dan setiap perubahan status
                    akan diinformasikan secara transparan melalui notifikasi.
                </div>
            </div>
        </div>
        <div x-data="{ isExpanded: false }">
            <button id="controlsAccordionItemOne" type="button"
                class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded"
                x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                    'text-on-surface dark:text-on-surface-dark font-medium'"
                x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                Layanan fotografi apa saja yang tersedia di Monopic Studio?
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                    stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                    x-bind:class="isExpanded ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                aria-labelledby="controlsAccordionItemOne" x-collapse>
                <div class="pb-4 text-sm sm:text-base text-pretty ">
                    Kami melayani berbagai kebutuhan fotografi, termasuk prewedding, dokumentasi acara, foto produk,
                    serta sesi photo studio lainnya. Setiap layanan tersedia dalam beberapa paket yang dapat disesuaikan
                    dengan kebutuhan Anda.
                </div>
            </div>
        </div>
        <div x-data="{ isExpanded: false }">
            <button id="controlsAccordionItemOne" type="button"
                class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded"
                x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                    'text-on-surface dark:text-on-surface-dark font-medium'"
                x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                Kapan hasil foto akan diterima setelah sesi pemotretan?
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                    stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                    x-bind:class="isExpanded ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                aria-labelledby="controlsAccordionItemOne" x-collapse>
                <div class="pb-4 text-sm sm:text-base text-pretty ">
                    Waktu pengerjaan hasil foto bergantung pada jenis layanan dan paket yang dipilih. Estimasi waktu
                    pengiriman akan diinformasikan sejak awal pemesanan agar Anda mendapatkan kejelasan dan kepastian.
                </div>
            </div>
        </div>
        <div x-data="{ isExpanded: false }">
            <button id="controlsAccordionItemOne" type="button"
                class="flex w-full items-center justify-between gap-4 py-4 text-left underline-offset-2 focus-visible:underline focus-visible:outline-hidden text-xl"
                aria-controls="accordionItemOne" x-on:click="isExpanded = ! isExpanded"
                x-bind:class="isExpanded ? 'text-on-surface-strong dark:text-on-surface-dark-strong font-semibold' :
                    'text-on-surface dark:text-on-surface-dark font-medium'"
                x-bind:aria-expanded="isExpanded ? 'true' : 'false'">
                Bagaimana cara menghubungi Monopic Studio untuk informasi lebih lanjut?
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2"
                    stroke="currentColor" class="size-5 shrink-0 transition" aria-hidden="true"
                    x-bind:class="isExpanded ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>
            <div x-cloak x-show="isExpanded" id="accordionItemOne" role="region"
                aria-labelledby="controlsAccordionItemOne" x-collapse>
                <div class="pb-4 text-sm sm:text-base text-pretty ">
                    Anda dapat menghubungi kami melalui halaman Kontak Kami di website, WhatsApp, atau email resmi
                    Monopic Studio. Tim kami siap membantu dan memberikan informasi yang Anda butuhkan dengan respons
                    yang cepat dan profesional.
                </div>
            </div>
        </div>

    </div>


</section>

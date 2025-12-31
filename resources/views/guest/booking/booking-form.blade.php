<?php

use App\Models\Package;
use Livewire\Volt\Component;

new class extends Component {
    //
    public Package $package;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $booking_date = '';
    public $booking_time = '';
    public $notes = '';




    public function with()
    {
        //
        return [
            //
        ];
    }


}; ?>

<div>
    <div class="space-y-5">
        <div class="space-y-2">
            <h1 class="text-4xl font-bold">Booking Sekarang</h1>
            <p class="text-sm font-light">Silakan isi formulir di bawah untuk melakukan pemesanan.</p>
        </div>
        <div class="space-y-4">
            @component('components.form.input', [
                'wireModel' => 'name',
                'label' => 'Nama Lengkap',
                'type' => 'text',
                'required' => true,

                ])

            @endcomponent
            @component('components.form.input', [
                'wireModel' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'required' => true,

                ])

            @endcomponent
            @component('components.form.input', [
                'wireModel' => 'phone',
                'label' => 'Nomor Telepon',
                'type' => 'text',
                'required' => true,

                ])

            @endcomponent
        </div>
    </div>
</div>

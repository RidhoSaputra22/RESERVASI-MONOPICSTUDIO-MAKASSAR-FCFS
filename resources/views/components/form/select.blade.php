<?php

use Livewire\Volt\Component;

new class extends Component {
    //
};

?>

<div class="input-form {{ $class ?? '' }}">
    <label for="{{ $name }}">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $name }}">
        @foreach ($options as $option)
            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
        @endforeach
    </select>
</div>

@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" target="_blank">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}"
                style="max-width: 50%; height: auto;">
        </a>

    </td>
</tr>

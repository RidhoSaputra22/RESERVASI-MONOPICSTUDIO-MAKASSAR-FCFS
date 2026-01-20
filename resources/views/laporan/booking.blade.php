<?php

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

new class extends Component
{
    use WithPagination;

    #[Url]
    public ?string $print = null;

    #[Url]
    public ?string $from = null;

    #[Url]
    public ?string $to = null;

    #[Url]
    public string $status = '';

    #[Url]
    public string $q = '';

    public int $perPage = 10;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('user.login');
        }
    }

    public function updated(string $name, mixed $value): void
    {
        if (in_array($name, ['from', 'to', 'status', 'q'], true)) {
            $this->resetPage();
        }
    }

    public function getIsPrintProperty(): bool
    {
        return in_array((string) ($this->print ?? ''), ['1', 'true', 'yes', 'on'], true);
    }

    public function clearFilters(): void
    {
        $this->reset(['from', 'to', 'status', 'q']);
        $this->resetPage();
    }

    protected function baseQuery(): Builder
    {
        $query = Booking::query();

        $user = Auth::user();
        if ($user && $user->role !== UserRole::Admin) {
            $query->where('user_id', $user->id);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if (! empty($this->from)) {
            $query->whereDate('scheduled_at', '>=', $this->from);
        }

        if (! empty($this->to)) {
            $query->whereDate('scheduled_at', '<=', $this->to);
        }

        if (trim($this->q) !== '') {
            $needle = trim($this->q);
            $query->where(function (Builder $q) use ($needle) {
                $q->where('code', 'like', "%{$needle}%")
                    ->orWhere('status', 'like', "%{$needle}%")
                    ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$needle}%"))
                    ->orWhereHas('package', fn (Builder $p) => $p->where('name', 'like', "%{$needle}%"));
            });
        }

        return $query;
    }

    public function with(): array
    {
        $listQuery = $this->baseQuery()
            ->with(['user', 'package', 'photographer', 'studio'])
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id');

        $summary = $this->baseQuery()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $total = array_sum($summary);

        if ($this->isPrint) {
            /** @var Collection<int, Booking> $items */
            $items = $listQuery->get();
            $bookings = new LengthAwarePaginator(
                $items,
                $items->count(),
                max(1, $items->count()),
                1,
                ['path' => request()->url(), 'query' => request()->query()],
            );
        } else {
            $bookings = $listQuery->paginate($this->perPage);
        }

        return [
            'bookings' => $bookings,
            'statuses' => BookingStatus::asArray(),
            'summary' => $summary,
            'total' => $total,
        ];
    }
};

?>

<div>
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-container { max-width: none !important; padding: 0 !important; }
            .print-card { border: none !important; box-shadow: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
        }
    </style>

    @if (! $this->isPrint)
        @livewire('layouts.navbar')
    @endif

    <div class="min-h-screen p-6 md:p-12 print-container">
        <div class="max-w-6xl mx-auto space-y-6">
            <div class="flex items-start justify-between gap-4 no-print">
                <div>
                    <h1 class="text-2xl font-semibold">Laporan Booking</h1>
                    <p class="text-sm text-gray-500">Data booking diambil langsung dari database.</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ request()->fullUrlWithQuery(['print' => '1']) }}"
                        class="px-4 py-2 rounded-md bg-white border hover:bg-gray-50">
                        Mode Print/PDF
                    </a>
                    <button type="button" onclick="window.print()" class="px-4 py-2 rounded-md bg-gray-900 text-white hover:bg-gray-800">
                        Print / Save PDF
                    </button>
                </div>
            </div>

            @if ($this->isPrint)
                <div class="print-card border bg-white p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h1 class="text-xl font-semibold">Laporan Booking</h1>
                            <p class="text-xs text-gray-600">Dicetak: {{ now()->format('d M Y H:i') }}</p>
                        </div>
                        <div class="text-right text-xs text-gray-600">
                            <div>Total: <span class="font-semibold text-gray-900">{{ $total }}</span></div>
                            <div>Pending: {{ $summary['pending'] ?? 0 }} | Confirmed: {{ $summary['confirmed'] ?? 0 }} | Completed: {{ $summary['completed'] ?? 0 }} | Cancelled: {{ $summary['cancelled'] ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-gray-700">
                        <span class="font-semibold">Filter:</span>
                        <span>
                            Tanggal: {{ $from ?: '-' }} s/d {{ $to ?: '-' }};
                            Status: {{ $status !== '' ? $status : 'Semua' }};
                            Kata kunci: {{ trim($q) !== '' ? $q : '-' }}
                        </span>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 no-print">
                <div class="p-4 rounded-lg border bg-white">
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="text-xl font-semibold">{{ $total }}</p>
                </div>
                <div class="p-4 rounded-lg border bg-white">
                    <p class="text-xs text-gray-500">Pending</p>
                    <p class="text-xl font-semibold">{{ $summary['pending'] ?? 0 }}</p>
                </div>
                <div class="p-4 rounded-lg border bg-white">
                    <p class="text-xs text-gray-500">Confirmed</p>
                    <p class="text-xl font-semibold">{{ $summary['confirmed'] ?? 0 }}</p>
                </div>
                <div class="p-4 rounded-lg border bg-white">
                    <p class="text-xs text-gray-500">Completed</p>
                    <p class="text-xl font-semibold">{{ $summary['completed'] ?? 0 }}</p>
                </div>
                <div class="p-4 rounded-lg border bg-white">
                    <p class="text-xs text-gray-500">Cancelled</p>
                    <p class="text-xl font-semibold">{{ $summary['cancelled'] ?? 0 }}</p>
                </div>
            </div>

            <div class="p-4 rounded-lg border bg-white space-y-4 no-print">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Dari Tanggal</label>
                        <input type="date" wire:model.live="from" class="w-full rounded-md border-gray-300" />
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Sampai Tanggal</label>
                        <input type="date" wire:model.live="to" class="w-full rounded-md border-gray-300" />
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Status</label>
                        <select wire:model.live="status" class="w-full rounded-md border-gray-300">
                            <option value="">Semua</option>
                            @foreach ($statuses as $st)
                            <option value="{{ $st['value'] }}">{{ $st['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Cari</label>
                        <input type="text" wire:model.live.debounce.300ms="q" placeholder="Kode / Nama paket / Nama"
                            class="w-full rounded-md border-gray-300" />
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" wire:click="clearFilters" class="text-sm text-gray-600 hover:underline">
                        Reset filter
                    </button>

                    <div class="text-sm text-gray-500">
                        Menampilkan {{ $bookings->firstItem() ?? 0 }}-{{ $bookings->lastItem() ?? 0 }} dari
                        {{ $bookings->total() }}
                    </div>
                </div>
            </div>

            <div class="rounded-lg border bg-white overflow-x-auto print-card">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="text-left px-4 py-3">Kode</th>
                            <th class="text-left px-4 py-3">Tanggal</th>
                            <th class="text-left px-4 py-3">Paket</th>
                            <th class="text-left px-4 py-3">Studio</th>
                            <th class="text-left px-4 py-3">Fotografer</th>
                            <th class="text-left px-4 py-3">Pemesan</th>
                            <th class="text-left px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($bookings as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">{{ $booking->code ?? '-' }}</td>
                            <td class="px-4 py-3">
                                {{ $booking->scheduled_at?->format('d M Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">{{ $booking->package?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $booking->studio?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $booking->photographer?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $booking->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700">
                                    {{ $booking->status?->getLabel() ?? ucfirst($booking->status?->value ?? '-') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                Data booking tidak ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (! $this->isPrint)
                <div>
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>

    @if (! $this->isPrint)
        @include('layouts.footter')
    @endif
</div>

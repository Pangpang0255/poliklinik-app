<x-layouts.app title="Notifikasi">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Notifikasi</h1>
            <p class="text-sm text-slate-500 mt-1">Semua pemberitahuan untuk akun Anda</p>
        </div>
        @if($notifications->total() > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit"
                class="btn btn-sm btn-outline gap-2 text-slate-600 border-slate-300 hover:bg-slate-100 hover:border-slate-400">
                <i class="fas fa-check-double text-xs"></i>
                Tandai Semua Dibaca
            </button>
        </form>
        @endif
    </div>

    <div class="space-y-3">

        @forelse($notifications as $notification)
        <div class="flex items-start gap-4 p-4 rounded-2xl border transition-all duration-200
            {{ $notification->is_read
                ? 'bg-white border-slate-100'
                : 'bg-blue-50/60 border-blue-200 shadow-sm' }}">

            {{-- Icon type --}}
            <div class="flex-shrink-0 mt-0.5">
                @if($notification->type === 'payment_verified')
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-circle-check text-green-500 text-sm"></i>
                </div>
                @elseif($notification->type === 'payment_created')
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-file-invoice-dollar text-amber-500 text-sm"></i>
                </div>
                @else
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-bell text-blue-500 text-sm"></i>
                </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 leading-tight">
                            {{ $notification->title }}
                            @if(!$notification->is_read)
                            <span class="inline-block w-2 h-2 bg-blue-500 rounded-full ml-1 mb-0.5"></span>
                            @endif
                        </p>
                        <p class="text-sm text-slate-600 mt-0.5">{{ $notification->message }}</p>
                        <p class="text-xs text-slate-400 mt-1">
                            <i class="fas fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                            &bull; {{ $notification->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>

                    @if(!$notification->is_read)
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit"
                            title="Tandai sudah dibaca"
                            class="btn btn-xs btn-ghost text-blue-500 hover:bg-blue-50 gap-1">
                            <i class="fas fa-check text-xs"></i>
                            Dibaca
                        </button>
                    </form>
                    @else
                    <span class="text-xs text-slate-300 flex items-center gap-1 flex-shrink-0 mt-0.5">
                        <i class="fas fa-check-double"></i> Dibaca
                    </span>
                    @endif
                </div>
            </div>
        </div>

        @empty
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-bell-slash text-3xl text-slate-300"></i>
            </div>
            <h3 class="text-slate-500 font-semibold text-lg">Belum Ada Notifikasi</h3>
            <p class="text-slate-400 text-sm mt-1">Notifikasi akan muncul di sini ketika ada aktivitas.</p>
        </div>
        @endforelse

    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
    @endif

</x-layouts.app>

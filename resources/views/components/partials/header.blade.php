<header class="bg-base-100 border-b border-base-300 h-16 flex items-center px-6 gap-4 sticky top-0 z-30 shadow-sm">

    @php
    $latestNotifications = auth()->user()
        ? auth()->user()->appNotifications()->latest()->take(5)->get()
        : collect();
    $unreadCount = auth()->user()
        ? auth()->user()->appNotifications()->where('is_read', false)->count()
        : 0;
    @endphp

    {{-- Mobile Hamburger --}}
    <button onclick="toggleSidebar()" class="btn btn-square btn-ghost lg:hidden">
        <i data-lucide="menu" class="w-5 h-5"></i>
    </button>

    {{-- Breadcrumb --}}
    <div class="flex-1">
        <div class="flex items-center gap-2 text-sm">
            <span class="text-base-content/50">Poliklinik</span>
            <i data-lucide="chevron-right" class="w-4 h-4 text-base-content/30"></i>
            <span class="font-semibold text-base-content">
                {{ $title ?? 'Dashboard' }}
            </span>
        </div>
    </div>

    {{-- Fullscreen --}}
    <button onclick="toggleFullscreen()" class="btn btn-square btn-ghost">
        <i id="fsIcon" class="fas fa-expand w-5 h-5"></i>
    </button>

    {{-- Notifications --}}
    <div class="dropdown dropdown-end">
        <button tabindex="0" class="btn btn-square btn-ghost relative">
            <i class="fas fa-bell w-5 h-5"></i>
            @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
            @endif
        </button>
        <div tabindex="0" class="dropdown-content z-60 mt-3 w-96 bg-white border border-slate-200 rounded-xl shadow-lg">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Notifikasi</h3>
                <div class="flex items-center gap-2">
                    @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-xs text-indigo-500 hover:text-indigo-700 font-medium">
                            Tandai semua dibaca
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('notifications.index') }}" class="text-xs text-slate-400 hover:text-slate-600">
                        Lihat semua
                    </a>
                </div>
            </div>

            <div class="max-h-80 overflow-y-auto">
                @forelse($latestNotifications as $notification)
                <div class="px-4 py-3 border-b border-slate-100 last:border-b-0 flex items-start gap-3 {{ $notification->is_read ? 'bg-white' : 'bg-blue-50/40' }}">
                    <div class="flex-shrink-0 mt-0.5">
                        @if($notification->type === 'payment_verified')
                        <div class="w-7 h-7 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check text-green-500 text-[10px]"></i>
                        </div>
                        @elseif($notification->type === 'payment_created')
                        <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center">
                            <i class="fas fa-file-invoice-dollar text-amber-500 text-[10px]"></i>
                        </div>
                        @else
                        <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-bell text-blue-500 text-[10px]"></i>
                        </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 leading-tight">
                            {{ $notification->title }}
                            @if(!$notification->is_read)
                            <span class="inline-block w-1.5 h-1.5 bg-blue-500 rounded-full ml-1 mb-0.5"></span>
                            @endif
                        </p>
                        <p class="text-xs text-slate-600 mt-0.5 line-clamp-2">{{ $notification->message }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="px-4 py-8 text-sm text-center text-slate-400">
                    <i class="fas fa-bell-slash text-2xl mb-2 block text-slate-200"></i>
                    Belum ada notifikasi.
                </div>
                @endforelse
            </div>

            @if($latestNotifications->isNotEmpty())
            <div class="px-4 py-2 border-t border-slate-100 text-center">
                <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-500 hover:text-indigo-700 font-medium">
                    Lihat semua notifikasi →
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- User Info --}}
    <div class="flex items-center gap-3">

        <div class="text-right hidden sm:block">
            <div class="text-sm font-semibold leading-tight">
                {{ auth()->user()->nama ?? auth()->user()->name ?? 'Pengguna' }}
            </div>
            <div class="text-xs text-base-content/50 leading-tight">
                {{ auth()->user()->role ?? 'Admin Sistem' }}
            </div>
        </div>

        <div class="avatar">
            <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
                <span class="text-sm font-semibold leading-none">
                    {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'U', 0, 1)) }}
                </span>
            </div>
        </div>
    </div>

</header>

<script>
    function toggleFullscreen() {
    const icon = document.getElementById('fsIcon');

    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        icon.classList.remove('fa-expand');
        icon.classList.add('fa-compress');
    } else {
        document.exitFullscreen();
        icon.classList.remove('fa-compress');
        icon.classList.add('fa-expand');
    }
}
</script>
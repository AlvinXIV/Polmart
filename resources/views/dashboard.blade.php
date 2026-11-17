@if (Auth::user()?->isAdmin())
    @include('admin.dashboard')
@else
    @include('anggota.dashboard')
@endif

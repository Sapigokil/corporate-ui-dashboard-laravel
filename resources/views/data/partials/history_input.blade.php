@if($events->count())
<hr class="horizontal dark my-4">

<h6 class="text-uppercase text-xs font-weight-bolder mb-3">
    Daftar Event
</h6>

<ul class="list-group">
@foreach ($events as $event)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ $event->deskripsi }}</strong><br>
            <small class="text-muted">
                {{ \Carbon\Carbon::parse($event->tanggal)->format('d M Y') }}
            </small>
        </div>

        <div class="d-flex gap-3">
            {{-- EDIT --}}
            <button class="btn btn-sm p-0 bg-transparent text-warning"
                data-bs-toggle="modal"
                data-bs-target="#editEvent{{ $event->id_event }}">
                <i class="fa-solid fa-pen fa-lg me-1"></i>
            </button>

            {{-- DELETE --}}
            <form action="{{ route('pengaturan.input.event.delete', $event->id_event) }}"
                method="POST"
                onsubmit="return confirm('Yakin hapus event ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm p-0 bg-transparent text-danger">
                    <i class="fa-solid fa-trash fa-lg me-1"></i>
                </button>
            </form>
        </div>
    </li>
@endforeach
</ul>
@endif

@if($notifications->count())
<hr class="horizontal dark my-4">

<h6 class="text-uppercase text-xs font-weight-bolder mb-3">
    Daftar Notifikasi
</h6>

<ul class="list-group">
@foreach ($notifications as $notif)
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ $notif->deskripsi }}</strong><br>
            <small class="text-muted">
                {{ \Carbon\Carbon::parse($notif->tanggal)->format('d M Y') }}
            </small>
        </div>

        <div class="d-flex gap-3">
            {{-- EDIT --}}
            <button class="btn btn-sm p-0 bg-transparent text-warning"
                data-bs-toggle="modal"
                data-bs-target="#editNotif{{ $notif->id_notifikasi }}">
                <i class="fa-solid fa-pen fa-lg me-1"></i>
            </button>

            {{-- DELETE --}}
            <form action="{{ route('pengaturan.input.notifikasi.delete', $notif->id_notifikasi) }}"
                method="POST"
                onsubmit="return confirm('Yakin hapus notifikasi ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm p-0 bg-transparent text-danger">
                    <i class="fa-solid fa-trash fa-lg me-1"></i>
                </button>
            </form>
        </div>
    </li>
@endforeach
</ul>
@endif

{{-- File: resources/views/vendor/pagination/soft-ui.blade.php --}}

@if ($paginator->hasPages())

<div class="container-fluid px-0">

    {{-- BARIS 1: Informasi Halaman (Rata Kiri) --}}
    <div class="row mb-2">
        <div class="col text-start text-sm text-muted">
            Menampilkan {{ $paginator->firstItem() }}
            sampai {{ $paginator->lastItem() }}
            dari total {{ $paginator->total() }} hasil
        </div>
    </div>

    {{-- BARIS 2: Pagination (Rata Tengah) --}}
    <div class="row">
        <div class="col d-flex justify-content-center">

            <nav role="navigation" aria-label="Pagination Navigation">
                <ul class="pagination pagination-primary mb-0">

                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">&lt;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}">&lt;</a>
                        </li>
                    @endif

                    {{-- Number Pages --}}
                    @foreach ($elements as $element)

                        @if (is_string($element))
                            <li class="page-item disabled">
                                <span class="page-link">{{ $element }}</span>
                            </li>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link bg-gradient-primary text-white border-0">
                                            {{ $page }}
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif

                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}">&gt;</a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link">&gt;</span>
                        </li>
                    @endif

                </ul>
            </nav>

        </div>
    </div>

</div>

@endif

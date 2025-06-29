<x-dashboard title="Add New Wallet">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add New Wallet</h1>
        <a href="{{ route('admin.wallets.index') }}" class="btn btn-info">
            <i class="fas fa-long-arrow-alt-left"></i> All Wallets
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.wallets.store') }}" method="POST">
                @csrf
                @include('admin.wallets._form')
                <button class="btn btn-success">
                    <i class="fas fa-save"></i> Save
                </button>
            </form>
        </div>
    </div>

    @push('css')
        <!-- Custom styles for this page -->
        <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    @endpush

    @push('js')
        <!-- Page level plugins -->
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <!-- Page level custom scripts -->
        <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    @endpush
</x-dashboard>

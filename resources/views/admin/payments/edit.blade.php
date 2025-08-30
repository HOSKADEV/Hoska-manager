<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Edit  Payment</h1>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-info"><i class="fas fa-long-arrow-alt-left"></i>All Payments</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.payments.update', $payment->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.payments._form')
                <button class='btn btn-success'><i class="fas fa-save"></i>Update</button>
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
        <script src="{{ asset("assets/js/demo/datatables-demo.js?v=" . time()) }}"></script>
    @endpush
</x-dashboard>


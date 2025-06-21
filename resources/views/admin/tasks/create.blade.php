<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add New Task</h1>
        <a href="{{ route('admin.tasks.index') }}" class="btn btn-info"><i class="fas fa-long-arrow-alt-left"></i>All
            Tasks</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.tasks.store') }}" method="POST">
                @csrf
                @include('admin.tasks._form')
                <button class='btn btn-success'><i class="fas fa-save"></i> Save</button>
            </form>
        </div>
    </div>

    @push('css')
        <!-- Custom styles for this page -->
        <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
        <!-- Flatpickr CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @endpush

    @push('js')
        <!-- Page level plugins -->
        <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <!-- Page level custom scripts -->
        <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
        <!-- Flatpickr JS -->
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script>
            flatpickr("#start_time", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
            });

            flatpickr("#end_time", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
            });
        </script>
    @endpush
</x-dashboard>

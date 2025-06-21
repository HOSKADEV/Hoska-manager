<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add New Employee</h1>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-info"><i
                class="fas fa-long-arrow-alt-left"></i>All Employees</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
            <strong>The following fields are required:</strong>
            <ul class="mb-0">
                @foreach ($errors->keys() as $field)
                    <li>{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.employees.store') }}" method="POST">
                @csrf
                @include('admin.employees._form')
                    <button class="btn btn-success"><i class="fas fa-save"></i> Save</button>
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
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const alert = document.getElementById('error-alert');
                if (alert) {
                    setTimeout(() => {
                        alert.remove(); // This will completely remove it from the DOM
                    }, 3000); // 3 seconds
                }
            });
        </script>
    @endpush
</x-dashboard>

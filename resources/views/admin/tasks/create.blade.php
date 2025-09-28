<x-dashboard title="Main Dashboard">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Add New Task</h1>
        <a href="{{ route('admin.tasks.index') }}" class="btn btn-info"><i class="fas fa-long-arrow-alt-left"></i>All
            Tasks</a>
    </div>

    <!-- Time Constraints Notice -->
    <div class="alert alert-info mb-4">
        <h5><i class="fas fa-info-circle"></i> Time Constraints</h5>
        <p>Please note the following time constraints when creating a task:</p>
        <ul>
            <li>The start time must be within 4 hours from the current time</li>
            <li>The duration between start time and end time must not exceed 4 hours</li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.tasks.store') }}" method="POST" id="task-form">
                @csrf
                @include('admin.tasks._form')
                <button type="submit" class='btn btn-success' id="save-btn"><i class="fas fa-save"></i> Save</button>
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
        <script src="{{ asset("assets/js/demo/datatables-demo.js?v=" . time()) }}"></script>
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

            // Prevent multiple form submissions
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('task-form');
                const saveBtn = document.getElementById('save-btn');

                if (form && saveBtn) {
                    form.addEventListener('submit', function() {
                        // Disable the save button to prevent multiple clicks
                        saveBtn.disabled = true;
                        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                        // Optional: Re-enable after 5 seconds in case of error
                        setTimeout(function() {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';
                        }, 5000);
                    });
                }
            });
        </script>
    @endpush
</x-dashboard>

<x-dashboard title="Main Dashboard">
    @push('css')
        <style>
            .badge-custom {
                display: inline-block;
                padding: 0.5em 0.8em;
                font-size: 0.9rem;
                font-weight: 600;
                color: #fff;
                border-radius: 0.5rem;
                white-space: nowrap;
                margin-right: 0.3em;
                margin-bottom: 0.2em;
                vertical-align: middle;
            }

            .badge-user {
                background-color: #fd7e14;
                /* برتقالي */
            }

            .badge-muted {
                background-color: #6c757d;
                /* رمادي */
            }
        </style>
    @endpush
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">All Employees</h1>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-info"><i class="fas fa-plus"></i>Add New</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            {{-- <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th> --}}
                            <th>Rate</th>
                            <th>Payment Type</th>
                            <th>User Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            {{-- <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th> --}}
                            <th>Rate</th>
                            <th>Payment Type</th>
                            <th>User Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>{{ $employee->id }}</td>
                                <td>{{ $employee->name }}</td>
                                {{-- <td>{{ $employee->contacts->first()->phone ?? '-' }}</td>
                                <td>{{ $employee->contacts->first()->email ?? '-' }}</td>
                                <td>{{ $employee->contacts->first()->address ?? '-' }}</td> --}}
                                <td>{{ $employee->rate }}</td>
                                <td>{{ $employee->payment_type }}</td>
                                <td>
                                    <span class="badge-custom badge-user">{{ auth()->user()->name }}</span>
                                </td>
                                <td>{{ $employee->created_at->diffForHumans() }}</td>
                                <td>{{ $employee->updated_at->diffForHumans() }}</td>
                                <td>
                                    <!-- زر عرض التفاصيل التواصل -->
                                    <button type="button" class="btn btn-sm btn-warning" data-toggle="modal"
                                        data-target="#personalModal{{ $employee->id }}">
                                        <i class="fas fa-address-book"></i>
                                    </button>
                                    <!-- زر عرض تفاصيل الدفع -->
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                        data-target="#paymentModal{{ $employee->id }}">
                                        <i class="fas fa-money-check-alt"></i>
                                    </button>
                                    {{-- زر عرض معلومات تسجيل الدخول --}}
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                        data-target="#loginModal{{ $employee->id }}">
                                        <i class="fas fa-user-lock"></i>
                                    </button>
                                    {{-- زر تعديل الموظف --}}
                                    <a href="{{ route('admin.employees.edit', $employee->id) }}"
                                        class="btn btn-sm btn-primary"><i class='fas fa-edit'></i>
                                    </a>
                                    {{-- زر حذف الموظف --}}
                                    <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST"
                                        style="display: inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Are you sure?!')" type="submit"
                                            class="btn btn-sm btn-danger"><i class='fas fa-trash'></i></button>
                                    </form>
                                </td>
                            </tr>
                            <!-- Payment Modal -->
                            <div class="modal fade" id="paymentModal{{ $employee->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="paymentModalLabel{{ $employee->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="paymentModalLabel{{ $employee->id }}">Payment
                                                Details - {{ $employee->name }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Rate:</strong> {{ $employee->rate }}</p>
                                            <p><strong>Payment Type:</strong> {{ ucfirst($employee->payment_type) }}</p>
                                            <p><strong>Account Name:</strong> {{ $employee->account_name ?? '_' }}</p>
                                            <p><strong>Account Number:</strong> {{ $employee->account_number ?? '_' }}</p>
                                            <p><strong>IBAN / RIB:</strong> {{ $employee->iban ?? '_' }}</p>
                                            <p><strong>Bank Code:</strong> {{ $employee->bank_code ?? '_' }}</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Personal Info Modal -->
                            <div class="modal fade" id="personalModal{{ $employee->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="personalModalLabel{{ $employee->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="personalModalLabel{{ $employee->id }}">Personal
                                                Details - {{ $employee->name }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Name:</strong> {{ $employee->name }}</p>
                                            <p><strong>Phone:</strong> {{ $employee->contacts->first()->phone ?? '_' }}</p>
                                            <p><strong>Email:</strong> {{ $employee->contacts->first()->email ?? '_' }}</p>
                                            <p><strong>Address:</strong> {{ $employee->contacts->first()->address ?? '_' }}
                                            </p>
                                            {{-- أضف أي بيانات شخصية أخرى حسب الحاجة --}}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Login Info Modal -->
                            <!-- Login Info Modal -->
                            <div class="modal fade" id="loginModal{{ $employee->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="loginModalLabel{{ $employee->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="loginModalLabel{{ $employee->id }}">
                                                Login Details - {{ $employee->name }}
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            @if($employee->user)
                                                <p><strong>Username:</strong> {{ $employee->user->name }}</p>
                                                <p><strong>Email:</strong> {{ $employee->user->email }}</p>
                                                <p><strong>User Type:</strong> {{ ucfirst($employee->user->type) }}</p>
                                            @else
                                                <p>No login information available.</p>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <tr>
                                <td class="d-none"></td>
                                <td colspan="11" class="text-center">No Data Found</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
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

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>{{ env("APP_NAME") }}</title>

    <!-- Custom fonts for this template-->
    <link rel="shortcut icon" href="{{asset('assets/img/icon')}}" type="image/x-icon">
    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('assets/css/sb-admin-2.min.css')}}" rel="stylesheet">
    @stack('css')
    <style>
        .colors {
            width: 100px;
            position: fixed;
            right: -60px;
            top: 150px;
            display: flex;
            transition: all 0.3s ease;
            z-index: 9999;
        }

        .colors.open {
            right: 0;
        }

        .colors button {
            background: #d9d9d9;
            border: 0;
            width: 40px;
            height: 40px;
        }

        .colors ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            background: #e4e4e4;
            width: 60px;
            justify-content: center;
            padding: 5px 0;
        }

        .colors ul li {
            width: 20px;
            height: 20px;
            margin: 3px;
            cursor: pointer;
        }
    </style>
</head>

<body id="page-top">

    <div class="colors">
        <button><i class="fas fa-cog"></i></button>
        <ul>
            <li class="bg-gradient-primary"></li>
            <li class="bg-gradient-dark"></li>
            <li class="bg-gradient-success"></li>
            <li class="bg-gradient-info"></li>
            <li class="bg-gradient-warning"></li>
            <li class="bg-gradient-danger"></li>
        </ul>
    </div>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="sidebar-brand-text mx-3">{{ env('APP_NAME') }}</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.index') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            @php
                $user = auth()->user();
            @endphp
            @auth
                @if($user->type === 'admin')

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Pages Collapse Menu Clients -->
                    <li
                        class="nav-item {{ request()->routeIs('admin.clients.index') || request()->routeIs('admin.clients.create') ? 'active' : ''}}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseClients"
                            aria-expanded="true" aria-controls="collapseClients">
                            <i class="fas fa-users"></i>
                            <span>Clients</span>
                        </a>
                        <div id="collapseClients"
                            class="collapse {{ request()->routeIs('admin.clients.index') || request()->routeIs('admin.clients.create') ? 'show' : ''}}"
                            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item  {{ request()->routeIs('admin.clients.index') ? 'active' : ''}}"
                                    href="{{ route('admin.clients.index') }}">All Clients</a>
                                <a class="collapse-item {{ request()->routeIs('admin.clients.create') ? 'active' : ''}}"
                                    href="{{ route('admin.clients.create') }}">Add New</a>
                            </div>
                        </div>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Pages Collapse Menu Employees -->
                    <li
                        class="nav-item {{ request()->routeIs('admin.employees.index') || request()->routeIs('admin.employees.create') ? 'active' : ''}}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseEmployees"
                            aria-expanded="true" aria-controls="collapseEmployees">
                            <i class="fas fa-user-tie"></i>
                            <span>Employees</span>
                        </a>
                        <div id="collapseEmployees"
                            class="collapse {{ request()->routeIs('admin.employees.index') || request()->routeIs('admin.employees.create') ? 'show' : ''}}"
                            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item {{ request()->routeIs('admin.employees.index') ? 'active' : ''}}"
                                    href="{{ route('admin.employees.index') }}">All Employees</a>
                                <a class="collapse-item {{ request()->routeIs('admin.employees.create') ? 'active' : ''}}"
                                    href="{{ route('admin.employees.create') }}">Add New</a>
                            </div>
                        </div>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Pages Collapse Menu Projects -->
                    <li
                        class="nav-item {{ request()->routeIs('admin.projects.index') || request()->routeIs('admin.projects.create') ? 'active' : ''}}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProjects"
                            aria-expanded="true" aria-controls="collapseProjects">
                            <i class="fas fa-project-diagram"></i>
                            <span>Projects</span>
                        </a>
                        <div id="collapseProjects"
                            class="collapse {{ request()->routeIs('admin.projects.index') || request()->routeIs('admin.projects.create') ? 'show' : ''}}"
                            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item  {{ request()->routeIs('admin.projects.index') ? 'active' : ''}}"
                                    href="{{ route('admin.projects.index') }}">All Projects</a>
                                <a class="collapse-item {{ request()->routeIs('admin.projects.create') ? 'active' : ''}}"
                                    href="{{ route('admin.projects.create') }}">Add New</a>
                            </div>
                        </div>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Pages Collapse Menu Projects -->
                    <li
                        class="nav-item {{ request()->routeIs('admin.developments.index') || request()->routeIs('admin.developments.create') ? 'active' : ''}}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseDevelopments"
                            aria-expanded="true" aria-controls="collapseDevelopments">
                            <i class="fas fa-code"></i>
                            <span>Developments</span>
                        </a>
                        <div id="collapseDevelopments"
                            class="collapse {{ request()->routeIs('admin.developments.index') || request()->routeIs('admin.developments.create') ? 'show' : ''}}"
                            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item  {{ request()->routeIs('admin.developments.index') ? 'active' : ''}}"
                                    href="{{ route('admin.developments.index') }}">All Developments</a>
                                <a class="collapse-item {{ request()->routeIs('admin.developments.create') ? 'active' : ''}}"
                                    href="{{ route('admin.developments.create') }}">Add New</a>
                            </div>
                        </div>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider">
                    <!-- Nav Item - Wallets Menu -->
                    <li
                        class="nav-item {{ request()->routeIs('admin.wallets.index') || request()->routeIs('admin.wallets.create') ? 'active' : '' }}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseWallets"
                            aria-expanded="true" aria-controls="collapseWallets">
                            <i class="fas fa-wallet"></i>
                            <span>Wallets</span>
                        </a>
                        <div id="collapseWallets"
                            class="collapse {{ request()->routeIs('admin.wallets.index') || request()->routeIs('admin.wallets.create') ? 'show' : '' }}"
                            aria-labelledby="headingWallets" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item {{ request()->routeIs('admin.wallets.index') ? 'active' : '' }}"
                                    href="{{ route('admin.wallets.index') }}">All Wallets</a>
                                <a class="collapse-item {{ request()->routeIs('admin.wallets.create') ? 'active' : '' }}"
                                    href="{{ route('admin.wallets.create') }}">Add New</a>
                            </div>
                        </div>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Wallet Transactions -->
                    <li class="nav-item {{ request()->routeIs('admin.wallet-transactions.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.wallet-transactions.index') }}">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Wallet Transactions</span>
                        </a>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Expenses -->
                    <li class="nav-item {{ request()->routeIs('admin.wallet-transactions.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.wallet-transactions.create') }}">
                            <i class="fas fa-arrow-circle-up"></i>
                            <span>Expense Transactions</span>
                        </a>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Pages Collapse Menu Invoices -->
                    <li
                        class="nav-item {{ request()->routeIs('admin.invoices.index') || request()->routeIs('admin.invoices.create') ? 'active' : ''}}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseInvoices"
                            aria-expanded="true" aria-controls="collapseInvoices">
                            <i class="fas fa-fw fa-newspaper"></i>
                            <span>Invoices</span>
                        </a>
                        <div id="collapseInvoices"
                            class="collapse {{ request()->routeIs('admin.invoices.index') || request()->routeIs('admin.invoices.create') ? 'show' : ''}}"
                            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item {{ request()->routeIs('admin.invoices.index') ? 'active' : ''}}"
                                    href="{{ route('admin.invoices.index') }}">All Invoices</a>
                                <a class="collapse-item {{ request()->routeIs('admin.invoices.create') ? 'active' : ''}}"
                                    href="{{ route('admin.invoices.create') }}">Add New</a>
                            </div>
                        </div>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Pages Collapse Menu Payments -->
                    <li
                        class="nav-item {{ request()->routeIs('admin.payments.index') || request()->routeIs('admin.payments.create') ? 'active' : ''}}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePayments"
                            aria-expanded="true" aria-controls="collapsePayments">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Payments</span>
                        </a>
                        <div id="collapsePayments"
                            class="collapse {{ request()->routeIs('admin.payments.index') || request()->routeIs('admin.payments.create') ? 'show' : ''}}"
                            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item {{ request()->routeIs('admin.payments.index') ? 'active' : ''}}"
                                    href="{{ route('admin.payments.index') }}">All Payments</a>
                                <a class="collapse-item {{ request()->routeIs('admin.payments.create') ? 'active' : ''}}"
                                    href="{{ route('admin.payments.create') }}">Add New</a>
                            </div>
                        </div>
                    </li>

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Pages Collapse Menu Tasks -->
                    <li
                        class="nav-item {{ request()->routeIs('admin.tasks.index') || request()->routeIs('admin.tasks.create') ? 'active' : ''}}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTasks"
                            aria-expanded="true" aria-controls="collapseTasks">
                            <i class="fas fa-tasks"></i>
                            <span>Tasks</span>
                        </a>
                        <div id="collapseTasks"
                            class="collapse {{ request()->routeIs('admin.tasks.index') || request()->routeIs('admin.tasks.create') ? 'show' : ''}}"
                            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item {{ request()->routeIs('admin.tasks.index') ? 'active' : ''}}"
                                    href="{{ route('admin.tasks.index') }}">All Tasks</a>
                                <a class="collapse-item {{ request()->routeIs('admin.tasks.create') ? 'active' : ''}}"
                                    href="{{ route('admin.tasks.create') }}">Add New</a>
                            </div>
                        </div>
                    </li>


                    <!-- Divider -->
                    {{--
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Pages Collapse Menu Timesheets -->
                    <li
                        class="nav-item {{ request()->routeIs('admin.timesheets.index') || request()->routeIs('admin.timesheets.create') ? 'active' : ''}}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTimesheets"
                            aria-expanded="true" aria-controls="collapseTimesheets">
                            <i class="fas fa-user-clock"></i>
                            <span>Timesheets</span>
                        </a>
                        <div id="collapseTimesheets"
                            class="collapse {{ request()->routeIs('admin.timesheets.index') || request()->routeIs('admin.timesheets.create') ? 'show' : ''}}"
                            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item {{ request()->routeIs('admin.timesheets.index') ? 'active' : ''}}"
                                    href="{{ route('admin.timesheets.index') }}">All Timesheets</a>
                                <a class="collapse-item {{ request()->routeIs('admin.timesheets.create') ? 'active' : ''}}"
                                    href="{{ route('admin.timesheets.create') }}">Add New</a>
                            </div>
                        </div>
                    </li> --}}

                    <!-- Divider -->
                    <hr class="sidebar-divider my-0">
                    <!-- Nav Item - Timesheets -->
                    <li class="nav-item {{ request()->routeIs('admin.timesheets.indexs') ? 'active' : ''}}">
                        <a class="nav-link" href="{{ route('admin.timesheets.index') }}">
                            <i class="fas fa-user-clock"></i>
                            <span>Timesheets</span></a>
                    </li>
                @endif
            @endauth

            <!-- عرض المهام فقط للموظف -->
            @auth
                @if($user->type === 'employee')
                    <li
                        class="nav-item {{ request()->routeIs('admin.tasks.index') || request()->routeIs('admin.tasks.create') ? 'active' : ''}}">
                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTasks"
                            aria-expanded="true" aria-controls="collapseTasks">
                            <i class="fas fa-tasks"></i>
                            <span>Tasks</span>
                        </a>
                        <div id="collapseTasks"
                            class="collapse {{ request()->routeIs('admin.tasks.index') || request()->routeIs('admin.tasks.create') ? 'show' : ''}}"
                            aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                            <div class="bg-white py-2 collapse-inner rounded">
                                <a class="collapse-item {{ request()->routeIs('admin.tasks.index') ? 'active' : ''}}"
                                    href="{{ route('admin.tasks.index') }}">All Tasks</a>
                                <a class="collapse-item {{ request()->routeIs('admin.tasks.create') ? 'active' : ''}}"
                                    href="{{ route('admin.tasks.create') }}">Add New</a>
                            </div>
                        </div>
                    </li>
                @endif
            @endauth

            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Pages Collapse Menu Notes -->
            <li
                class="nav-item {{ request()->routeIs('admin.notes.index') || request()->routeIs('admin.notes.create') ? 'active' : ''}}">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseNotes"
                    aria-expanded="true" aria-controls="collapseNotes">
                    <i class="fas fa-clipboard"></i>
                    <span>Notes</span>
                </a>
                <div id="collapseNotes"
                    class="collapse {{ request()->routeIs('admin.notes.index') || request()->routeIs('admin.notes.create') ? 'show' : ''}}"
                    aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item {{ request()->routeIs('admin.notes.index') ? 'active' : ''}}"
                            href="{{ route('admin.notes.index') }}">All Notes</a>
                        <a class="collapse-item {{ request()->routeIs('admin.notes.create') ? 'active' : ''}}"
                            href="{{ route('admin.notes.create') }}">Add New</a>
                    </div>
                </div>
            </li>
            {{--
            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Pages Collapse Menu Contacts -->
            <li
                class="nav-item {{ request()->routeIs('admin.contacts.index') || request()->routeIs('admin.contacts.create') ? 'active' : ''}}">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseContacts"
                    aria-expanded="true" aria-controls="collapseContacts">
                    <i class="fas fa-fw fa-newspaper"></i>
                    <span>Contacts</span>
                </a>
                <div id="collapsecontacts"
                    class="collapse {{ request()->routeIs('admin.contacts.index') || request()->routeIs('admin.contacts.create') ? 'show' : ''}}"
                    aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item {{ request()->routeIs('admin.contacts.index') ? 'active' : ''}}"
                            href="{{ route('admin.contacts.index') }}">All Contacts</a>
                        <a class="collapse-item {{ request()->routeIs('admin.contacts.create') ? 'active' : ''}}"
                            href="{{ route('admin.contacts.create') }}">Add New</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Notifications -->
            <li class="nav-item {{ request()->routeIs('admin.notifications') ? 'active' : ''}}">
                <a class="nav-link" href="{{ route('admin.notifications') }}">
                    <i class="fas fa-fw fa-cogs"></i>
                    <span>Notifications</span></a>
            </li> --}}

            {{-- <!-- Divider -->
            <hr class="sidebar-divider my-0">
            <!-- Nav Item - Contacts -->
            <li class="nav-item {{ request()->routeIs('admin.contact_messages') ? 'active' : ''}}">
                <a class="nav-link" href="{{ route('admin.contact_messages') }}">
                    <i class="fas fa-fw fa-comments"></i>
                    <span>Contacts Message</span>
                    <span style="top: 25px; right: 20px;" class="badge badge-danger badge-counter ">{{
                        $unread_messages_count }}</span></a>
            </li> --}}

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Messages -->
                        {{-- <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-envelope fa-fw"></i>
                                <!-- Counter - Messages -->
                                <span class="badge badge-danger badge-counter">{{ $unread_messages_count }}</span>

                            </a>
                            <!-- Dropdown - Messages -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="messagesDropdown">
                                <h6 class="dropdown-header">
                                    Message Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="{{ asset('assets/img/undraw_profile_1.svg') }}"
                                            alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div class="font-weight-bold">
                                        <div class="text-truncate">Hi there! I am wondering if you can help me with a
                                            problem I've been having.</div>
                                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="{{ asset('assets/img/undraw_profile_2.svg') }}"
                                            alt="...">
                                        <div class="status-indicator"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">I have the photos that you ordered last month, how
                                            would you like them sent to you?</div>
                                        <div class="small text-gray-500">Jae Chun · 1d</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="{{ asset('assets/img/undraw_profile_3.svg') }}"
                                            alt="...">
                                        <div class="status-indicator bg-warning"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Last month's report looks great, I am very happy with
                                            the progress so far, keep up the good work!</div>
                                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="dropdown-list-image mr-3">
                                        <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60"
                                            alt="...">
                                        <div class="status-indicator bg-success"></div>
                                    </div>
                                    <div>
                                        <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                                            told me that people say this to all dogs, even if they aren't good...</div>
                                        <div class="small text-gray-500">Chicken the Dog · 2w</div>
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
                            </div>
                        </li> --}}

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ Auth::user()->name
                                    }}</span>
                                @php
                                    $path = Auth::user()->avatar ? Auth::user()->avatar : 'assets/img/undraw_profile.svg';
                                @endphp
                                <img style='object-fit:cover' class="img-profile rounded-circle"
                                    src="{{ asset($path) }}">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                {{-- <a class="dropdown-item" href="{{ route('admin.settings') }}">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a> --}}
                                <div class="dropdown-divider"></div>
                                <a onclick="event.preventDefault(); document.querySelector('#logout-form').submit() "
                                    class="dropdown-item" href="{{ route('logout') }}">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                    @csrf
                                </form>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    {{ $slot }}

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; {{ config('app.name') }} {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="login.html">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('assets/js/sb-admin-2.min.js') }}"></script>
    {{--
    <script>
        document.querySelector('.colors button').onclick = () => {
            document.querySelector('.colors').classList.toggle('open')
        }

        // log
        document.querySelectorAll('.colors ul li').forEach(el => {
            el.onclick = () => {
                let cl = el.classList[0];
                document.querySelector('#accordionSidebar').className = '';
                document.querySelector('#accordionSidebar').classList.add(cl)
                localStorage.setItem('cl', cl)
            }
        });


        let oldclass = localStorage.getItem('cl') ?? 'bg-gradient-primary'
        document.querySelector('#accordionSidebar').classList.add(oldclass)

    </script> --}}

    <script>
            (() => {
                const colorsBtn = document.querySelector('.colors button');
                const colorsList = document.querySelector('.colors');
                const colorItems = document.querySelectorAll('.colors ul li');
                const sidebar = document.getElementById('accordionSidebar');

                const colorClasses = [
                    'bg-gradient-primary',
                    'bg-gradient-dark',
                    'bg-gradient-success',
                    'bg-gradient-info',
                    'bg-gradient-warning',
                    'bg-gradient-danger'
                ];

                // فتح وإغلاق صندوق الألوان
                colorsBtn.addEventListener('click', () => {
                    colorsList.classList.toggle('open');
                    // تحديث aria-expanded
                    const expanded = colorsList.classList.contains('open');
                    colorsBtn.setAttribute('aria-expanded', expanded);
                });

                // تحميل اللون المخزن
                const savedClass = localStorage.getItem('sidebarColor') || 'bg-gradient-primary';

                // إزالة أصناف الألوان القديمة
                function removeColorClasses() {
                    colorClasses.forEach(c => sidebar.classList.remove(c));
                }

                // تطبيق اللون المخزن عند التحميل
                removeColorClasses();
                sidebar.classList.add(savedClass);

                // عند اختيار لون جديد
                colorItems.forEach(item => {
                    item.addEventListener('click', () => {
                        const selectedClass = item.classList[0];
                        removeColorClasses();
                        sidebar.classList.add(selectedClass);
                        localStorage.setItem('sidebarColor', selectedClass);
                    });
                });
            })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('js')
</body>

</html>

<x-dashboard title="Main Dashboard">
    <style>
        .bg {
            position: relative;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Adjust the opacity as needed */
        }
        .bg h1, .bg p {
            position: relative;
            z-index: 1; /* Ensure text is above the background */
        }
        .bg a {
            position: relative;
            z-index: 1; /* Ensure button is above the background */
        }
        .bg img {
            width: 100%;
            height: 50vh;
            object-fit: cover;
            margin: auto;
        }
    </style>
    <div class="card">
        <div class="card-body">
            <div class="bg" style="width: 100%; height: 70vh; object-fit: cover; margin: auto; background-image: url('{{ asset('assets/img/bg.jpg') }}'); background-color: rgba(0, 0, 0, 0.5);">

            <div class="text-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                <h1 class="text-white">Welcome to the Admin Dashboard</h1>
                <p class="text-white">This is a blank page template. You can customize it as per your requirements.</p>
                <a href="{{ route('admin.developments.index') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Go to Developments</a>
        </div>
    </div>
</x-dashboard>

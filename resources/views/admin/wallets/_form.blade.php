<div class="mb-3">
    <x-form.input label="Wallet Name" name="name" placeholder="Enter Wallet Name" :oldval="$wallet->name ?? ''" />
</div>


@if(!isset($wallet)) {{-- Show only on create --}}
    <div class="mb-3">
        <x-form.input label="Initial Amount" name="initial_amount" placeholder="Enter Initial Amount"
            :oldval="old('initial_amount')" />
    </div>
@endif

<div class="mb-3">
    <x-form.select2 label="Currency" name="currency" :options="['EUR' => 'Euro', 'USD' => 'US Dollar', 'DZD' => 'Algerian Dinar']" :selected="$wallet->currency ?? old('currency')" placeholder="Select currency" />
</div>

<div class="mb-3">
    <x-form.area label="Notes" name="notes" placeholder="Enter Notes" :oldval="$wallet->notes ?? ''" />
</div>

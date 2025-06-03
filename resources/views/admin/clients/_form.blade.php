<div class="mb-3">
    <x-form.input label="Name" name="name" placeholder="Enter Client Name" :oldval="$client->name" />
</div>
<div class="mb-3">
    <x-form.input label="Phone" name="phone" placeholder="Enter Client Phone" :oldval="$client->phone" />
</div>
<div class="mb-3">
    <x-form.input label="Email" name="email" placeholder="Enter Client Email" :oldval="$client->email" />
</div>
<div class="mb-3">
    <x-form.input label="Address" name="address" placeholder="Enter Client Address" :oldval="$client->address" />
</div>

<div class="mb-3">
    <x-form.area label="Notes" name="notes" placeholder="Enter Client Notes"
        :oldval="$client->notes" />
</div>

<div class="mb-3">
    <x-form.select label="User" name="user_id" placeholder='Select User' :options="$users"
        :oldval="$client->user_id" />
</div>



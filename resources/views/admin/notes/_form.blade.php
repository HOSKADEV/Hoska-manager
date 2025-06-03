<div class="mb-3">
    <x-form.area label="Notes" name="note" placeholder="Enter Note Notes"
        :oldval="$note->note" />
</div>

<div class="mb-3">
    <x-form.select label="User" name="user_id" placeholder='Select User' :options="$users"
        :oldval="$note->user_id" />
</div>



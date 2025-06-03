<div class="mb-3">
    <x-form.area label="Description" name="description" placeholder="Enter Client Description"
        :oldval="$development->description" />
</div>

<div class="mb-3">
    <x-form.input label="Amount" name="amount" placeholder="Enter Development Amount" :oldval="$development->amount" />
</div>


<div class="mb-3">
    <x-form.select label="Project" name="project_id" placeholder='Select Project' :options="$projects"
        :oldval="$development->project_id" />
</div>

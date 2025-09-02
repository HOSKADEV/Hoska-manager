<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OurTask extends Model
{
    protected $fillable = [
        'title',
        'project_id',
        'description',
        'duration',
        'cost'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Calculate the duration in days from the duration text
     * 
     * @return int
     */
    public function getDurationInDaysAttribute()
    {
        if (!$this->duration) return 0;

        // Handle different duration formats (e.g., "2 days", "1 week", "3 hours")
        $duration = strtolower($this->duration);

        if (strpos($duration, 'day') !== false) {
            return (int) preg_replace('/[^0-9]/', '', $duration);
        } elseif (strpos($duration, 'week') !== false) {
            return (int) preg_replace('/[^0-9]/', '', $duration) * 7;
        } elseif (strpos($duration, 'hour') !== false) {
            return (int) preg_replace('/[^0-9]/', '', $duration) / 24; // Convert hours to days (approximately)
        }

        return 0;
    }

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::created(function ($task) {
            self::updateProjectDeliveryDate($task);
        });

        static::updated(function ($task) {
            self::updateProjectDeliveryDate($task);
        });

        static::deleted(function ($task) {
            self::updateProjectDeliveryDate($task);
        });
    }

    /**
     * Update the project delivery date based on task durations
     * 
     * @param OurTask $task
     * @return void
     */
    private static function updateProjectDeliveryDate($task)
    {
        $project = $task->project;
        if (!$project) return;

        // Only update if the project doesn't have a fixed delivery date or is manual
        if ($project->is_manual) return;

        // Calculate total duration from all tasks
        $totalDays = 0;
        foreach ($project->ourTasks as $task) {
            $totalDays += $task->duration_in_days;
        }

        // If we have a start date, calculate the new delivery date
        if ($project->start_date && $totalDays > 0) {
            $startDate = Carbon::parse($project->start_date);
            $deliveryDate = $startDate->addDays($totalDays);

            $project->delivery_date = $deliveryDate->format('Y-m-d');
            $project->duration_days = $totalDays;
            $project->save();
        }
    }
}

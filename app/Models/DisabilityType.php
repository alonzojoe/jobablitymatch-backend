<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisabilityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status'
    ];

    public function jobPostings()
    {
        return $this->belongsToMany(JobPosting::class, 'job_posting_disability_types')->withTimestamps();
    }
}

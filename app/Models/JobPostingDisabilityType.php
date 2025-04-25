<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPostingDisabilityType extends Model
{
    use HasFactory;

    protected $fillable = ['job_posting_id', 'disability_type_id', 'status'];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function disabilityType()
    {
        return $this->belongsTo(DisabilityType::class);
    }
}

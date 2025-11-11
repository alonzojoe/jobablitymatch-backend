<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'vacant_positions', 'company_id', 'active', 'status', 'hiring_from', 'hiring_to'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }

    public function disabilityTypes()
    {
        return $this->belongsToMany(DisabilityType::class, 'job_posting_disability_types')->withTimestamps();
    }
}

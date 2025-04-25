<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'company_id', 'active', 'status'];

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

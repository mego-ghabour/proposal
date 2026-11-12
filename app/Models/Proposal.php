<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Proposal extends Model
{
    protected $fillable = [
        'uuid',
        'slug',
        'title',
        'client_name',
        'client_company',
        'client_logo_url',
        'brand_color',
        'client_email',
        'client_phone',
        'sales_rep_name',
        'sales_phone',
        'currency',
        'status',
        'raw_idea',
        'clarifications',
        'executive_summary',
        'problem_vs_solution',
        'workflow_steps',
        'system_architecture',
        'modules',
        'pricing_packages',
        'payment_milestones',
        'timeline_phases',
        'deliverables_and_guarantees',
        'terms_and_conditions',
        'views_count',
        'last_viewed_at',
        'accepted_at',
        'accepted_package_name',
        'client_notes',
        'valid_until',
    ];

    protected $casts = [
        'clarifications' => 'array',
        'executive_summary' => 'array',
        'problem_vs_solution' => 'array',
        'workflow_steps' => 'array',
        'system_architecture' => 'array',
        'modules' => 'array',
        'pricing_packages' => 'array',
        'payment_milestones' => 'array',
        'timeline_phases' => 'array',
        'deliverables_and_guarantees' => 'array',
        'terms_and_conditions' => 'array',
        'last_viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'valid_until' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($proposal) {
            if (empty($proposal->uuid)) {
                $proposal->uuid = (string) Str::uuid();
            }
            if (empty($proposal->slug)) {
                $proposal->slug = Str::slug($proposal->title ?? 'proposal') . '-' . Str::random(6);
            }
        });
    }

    /**
     * Get the shareable public link.
     */
    public function getShareUrlAttribute(): string
    {
        return url('/p/' . $this->uuid);
    }

    /**
     * Get WhatsApp share text.
     */
    public function getWhatsAppShareTextAttribute(): string
    {
        return "مرحباً عزيزي/عزيزتي {$this->client_name}،\nيسعدنا في وكالة فكرتك الرقمية تقديم العرض الفني والمالي الخاص بمشروعكم:\n{$this->title}\n\nيمكنكم الاطلاع على تفاصيل العرض والجدول الزمني عبر الرابط:\n{$this->share_url}";
    }
}

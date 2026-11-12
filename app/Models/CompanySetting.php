<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'agency_name',
        'agency_tagline',
        'logo_url',
        'icon_url',
        'phone',
        'whatsapp',
        'email',
        'website',
        'address',
        'social_links',
        'default_terms',
        'default_guarantees',
        'gemini_api_key',
        'gemini_model',
    ];

    protected $casts = [
        'social_links' => 'array',
        'default_terms' => 'array',
        'default_guarantees' => 'array',
    ];

    /**
     * Retrieve the active company settings instance (singleton style).
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'agency_name' => 'وكالة فكرتك الرقمية - Fkretk Agency',
            'agency_tagline' => 'شريكك التقني لتطوير المنظومات والتطبيقات المبتكرة',
            'logo_url' => '/assets/logo_fkretk.png',
            'icon_url' => '/assets/fkretk_icon_512.png',
            'phone' => '+966500000000',
            'whatsapp' => '+966500000000',
            'email' => 'info@fkretk.com',
            'website' => 'https://fkretk.com',
            'address' => 'الرياض - القاهرة',
            'social_links' => [
                'website' => 'https://fkretk.com',
                'linkedin' => 'https://linkedin.com/company/fkretk',
                'twitter' => 'https://twitter.com/fkretk',
                'instagram' => 'https://instagram.com/fkretk',
                'behance' => 'https://behance.net/fkretk',
            ],
            'default_terms' => [
                'تسليم سورس كود كامل وموثق بدون أي قيود أو اشتراكات شهرية متكررة للبرمجة.',
                'سرعة استجابة تقنية على مدار الساعة خلال فترة الدعم الفني المجاني.',
                'ضمان استقرار السيرفرات وتطبيق أعلى معايير الحماية والأمان السيبراني.',
                'صلاحية هذا العرض سارية لمدة 14 يوماً من تاريخ إصداره.',
            ],
            'default_guarantees' => [
                'ضمان فني شامل لمدة 6 أشهر بعد الإطلاق.',
                'تدريب كامل لفريق العمل وإدارة المنظومة.',
                'توثيق تقني ومعماري شامل لجميع العمليات والـ APIs.',
            ],
            'gemini_api_key' => env('GEMINI_API_KEY', ''),
            'gemini_model' => env('GEMINI_MODEL', 'gemini-3.8-flash'),
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    use HasFactory;

    /**
     * テーブル名
     */
    protected $table = 'daily_summaries';

    /**
     * 一括割り当て可能な属性
     */
    protected $fillable = [
        'company_id',
        'employee_id',
        'date',
        'total_work_hours',
        'total_break_hours',
        'overtime_hours',
        'salary',
        'error_types',
    ];

    /**
     * 日付型として扱うカラム
     */
    protected $dates = [
        'date',
        'created_at',
        'updated_at',
    ];

    /**
     * カンマ区切りのエラー種別を配列として扱うアクセサ
     */
    public function getErrorTypesAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    /**
     * エラー種別を保存する際にカンマ区切りで保存するミューテータ
     */
    public function setErrorTypesAttribute($value)
    {
        $this->attributes['error_types'] = is_array($value) ? implode(',', $value) : $value;
    }
}

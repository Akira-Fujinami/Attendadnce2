<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adit extends Model
{
    use HasFactory;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'adit_logs';

    /**
     * 一括代入可能な属性
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'employee_id',
        'event_id',
        'date',
        'minutes',
        'adit_item',
        'status',
        'before_adit_id',
        'deleted',
    ];

    /**
     * 日付としてキャストする属性
     *
     * @var array
     */
    protected $dates = [
        'date',
        'minutes',
    ];

    /**
     * リレーション: Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * リレーション: Employee
     */

     public function employee()
     {
         return $this->belongsTo(Employee::class, 'employee_id', 'id');
     }
}

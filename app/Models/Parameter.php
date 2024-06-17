<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parameter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category',
        'code',
        'type_id',
        'value',
        'mode_id',
        'help',
        'comments',
        'created_by',
        'updated_by',
    ];

    /**
     * Type
     *
     * @var array
     */
    public const TYPES = [
        0 => 'string',
        1 => 'integer',
        2 => 'boolean',
        3 => 'date',
        4 => 'datetime',
        5 => 'time',
        6 => 'timestamp',
        7 => 'text',
    ];

    /**
     * Mode
     *
     * @var array
     */
    public const MODES = [
        'editable',
        'readonly',
        'internal',
    ];

    /**
     * returns the id of a given type
     *
     * @param string $code code
     * @return int codeID
     */
    public static function getTypeID($code)
    {
        return array_search($code, self::TYPES);
    }

    /**
     * returns the id of a given mode
     *
     * @param string $code code
     * @return int codeID
     */
    public static function getModeID($code)
    {
        return array_search($code, self::MODES);
    }

    /**
     * get type
     */
    public function getTypeAttribute()
    {
        if (isset($this->attributes['type_id'])) {
            return self::TYPES[ $this->attributes['type_id'] ];
        } else {
            return self::TYPES[0];
        }
    }

    /**
     * get mode
     */
    public function getModeAttribute()
    {
        if (isset($this->attributes['mode_id'])) {
            return self::MODES[ $this->attributes['mode_id'] ];
        } else {
            return self::MODES[0];
        }
    }

    /**
     * set type
     */
    public function setTypeAttribute($value)
    {
        $this->attributes['type_id'] = self::getTypeID($value);
    }

    /**
     * set mode
     */
    public function setModeAttribute($value)
    {
        $this->attributes['mode_id'] = self::getModeID($value);
    }

    public function created_byx(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updated_byx(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function nickname()
    {
        return nickname($this->attributes['id']);
    }
}

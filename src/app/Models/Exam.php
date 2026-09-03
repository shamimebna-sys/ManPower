<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Exam extends Model
{
    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id', 'id')->withDefault(function ($instance, $parent) {
            $ids = array_filter(explode(',', $parent->class_group_id));
            if (!empty($ids)) {
                $groups = ClassGroup::whereIn('id', $ids)->get();
                $instance->group_info = $groups->map(function ($g) {
                    return $g->group_info;
                })->implode(', ');
            }
            return $instance;
        });
    }
}

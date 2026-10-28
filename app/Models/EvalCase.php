<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvalCase extends Model
{
    protected $fillable = ['question', 'relevant_ids'];

    protected function casts(): array
    {
        return ['relevant_ids' => 'array'];
    }

    /** @return list<string> */
    public function relevantIds(): array
    {
        $rawIds = $this->getAttribute('relevant_ids');
        $ids = [];

        if (is_array($rawIds)) {
            foreach ($rawIds as $id) {
                if (is_string($id)) {
                    $ids[] = $id;
                }
            }
        }

        return $ids;
    }
}

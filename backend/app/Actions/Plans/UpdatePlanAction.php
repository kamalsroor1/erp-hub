<?php

namespace App\Actions\Plans;

use App\Models\Plan;

class UpdatePlanAction
{
    public function execute(Plan $plan, array $data): Plan
    {
        $plan->update($data);

        return $plan;
    }
}

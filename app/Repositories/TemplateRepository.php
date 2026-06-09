<?php

namespace App\Repositories;

use App\Models\Template;

class TemplateRepository extends AbstractRepository
{
    public function getModelClass()
    {
        return Template::class;
    }
}

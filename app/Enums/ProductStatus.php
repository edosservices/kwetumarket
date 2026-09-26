<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ProductStatus: string
{
    use HasValues;

    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';
    case Archived = 'archived';
}

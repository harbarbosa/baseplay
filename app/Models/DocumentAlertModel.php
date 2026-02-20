<?php

namespace App\Models;

use CodeIgniter\Model;

class DocumentAlertModel extends Model
{
    protected $table = 'document_alerts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'document_id',
        'alert_date',
        'sent_at',
        'created_at',
    ];
}
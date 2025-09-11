<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignatureDocument extends Model
{

protected $fillable = [
    'signature_request_id',
    'orig_name',
    'orig_path',
    'signature_png_path',  
    'signed_pdf_path',
    'signed_at',
];


    public function request()
    {
        return $this->belongsTo(SignatureRequest::class, 'signature_request_id');
    }
}

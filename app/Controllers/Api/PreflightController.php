<?php

namespace App\Controllers\Api;

class PreflightController extends BaseApiController
{
    public function index()
    {
        return service('response')->setStatusCode(204);
    }
}


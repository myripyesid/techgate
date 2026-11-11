<?php

namespace App\Contracts;

interface ReportInterface
{
    public function generate(array $data);
}
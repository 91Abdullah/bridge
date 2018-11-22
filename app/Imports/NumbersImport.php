<?php

namespace App\Imports;

use App\IncomingNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class NumbersImport implements ToModel, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new IncomingNumber([
            'number' => $row[0]
        ]);
    }

    public function rules(): array
    {
        return [
            '0' => 'required|numeric|unique:incoming_numbers,number'
        ];
    }
}

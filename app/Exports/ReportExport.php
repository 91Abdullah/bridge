<?php

namespace App\Exports;

use App\Record;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromQuery, WithHeadings
{
    use Exportable;

    private $from;
    private $to;

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        return Record::query()->whereBetween('start', [Carbon::parse($this->from)->format('Y-m-d'), Carbon::parse($this->to)->format('Y-m-d')])->select(['id', 'source', 'destination', 'start', 'answer', 'end', 'duration', 'billsec', 'dialstatus', 'amount', 'pin_code']);
    }

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function headings() : array
    {
        return [
            '#',
            'Source',
            'Destination',
            'Start',
            'Answer',
            'End',
            'Duration',
            'Bill Sec',
            'Dial Status',
            'Amount',
            'PIN'
        ];
    }
}

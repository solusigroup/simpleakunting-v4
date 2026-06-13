<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinancialReportExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $view;
    protected $data;
    protected $title;

    public function __construct(string $view, array $data, string $title = 'Laporan')
    {
        $this->view = $view;
        $this->data = $data;
        $this->title = $title;
    }

    public function view(): View
    {
        return view($this->view, $this->data);
    }

    public function title(): string
    {
        return $this->title;
    }
}

<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class BaseExport
{
    public function __construct(protected Collection $records) {}

    abstract public function title(): string;

    abstract public function headings(): array;

    abstract public function map($record): array;

    public function rows(): Collection
    {
        return $this->records->map(fn ($record) => $this->map($record));
    }

    protected function filename(string $extension): string
    {
        return Str::slug($this->title()).'-'.now()->format('Y-m-d').'.'.$extension;
    }

    public function toCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->headings());
            foreach ($this->rows() as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $this->filename('csv'), ['Content-Type' => 'text/csv']);
    }

    public function toPdf()
    {
        return Pdf::loadView('pdf.report', [
            'title' => $this->title(),
            'headings' => $this->headings(),
            'rows' => $this->rows(),
        ])->setPaper('a4', 'landscape')->download($this->filename('pdf'));
    }
}

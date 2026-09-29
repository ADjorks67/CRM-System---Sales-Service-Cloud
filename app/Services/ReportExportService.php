<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * @param  list<string>  $columnLabels
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function csv(string $filename, array $columns, array $columnLabels, Collection $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($columns, $columnLabels, $rows): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, $columnLabels);

            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $column) {
                    $line[] = $row[$column] ?? '';
                }
                fputcsv($handle, $line);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

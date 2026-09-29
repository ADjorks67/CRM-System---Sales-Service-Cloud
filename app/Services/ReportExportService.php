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
        $content = $this->csvContent($columns, $columnLabels, $rows);

        return response()->streamDownload(function () use ($content): void {
            echo $content;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Build CSV string content for email attachments (FR-RPT-006).
     *
     * @param  list<string>  $columns
     * @param  list<string>  $columnLabels
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function csvContent(array $columns, array $columnLabels, Collection $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return '';
        }

        fputcsv($handle, $columnLabels);

        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $column) {
                $line[] = $row[$column] ?? '';
            }
            fputcsv($handle, $line);
        }

        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
    }
}

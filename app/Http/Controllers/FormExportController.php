<?php

namespace App\Http\Controllers;

use App\Models\Form;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

class FormExportController extends Controller
{
    public function __invoke(Form $form)
    {
        abort_if($form->user_id !== auth()->id(), 403);

        // Pull the export state that FormsIndex stored just before redirect
        $state        = session()->pull("form_export_{$form->id}", []);
        $statusFilter = $state['statusFilter'] ?? null;
        $colOrder     = $state['colOrder']     ?? [];
        $colVisible   = $state['colVisible']   ?? [];

        $form->load([
            'columns'        => fn ($q) => $q->orderBy('order'),
            'entries.values',
            'entries.status',
            'owner',
        ]);

        // ── Resolve column state (mirror FormsIndex logic) ────────────────────
        $allColIds = $form->columns->pluck('id')->map(fn ($v) => (string) $v)->toArray();

        $systemKeys = ['status', 'source', 'owner'];

        if (empty($colOrder)) {
            $colOrder = array_merge($allColIds, $systemKeys);
        }
        if (empty($colVisible)) {
            $colVisible = array_fill_keys($allColIds, true) + array_fill_keys($systemKeys, true);
        }

        $colById = $form->columns->keyBy('id');

        $items = collect($colOrder)
            ->map(function ($key) use ($colById, $colVisible) {
                if (!($colVisible[$key] ?? true)) return null;
                if ($key === 'status') return ['type' => 'status', 'label' => 'Status'];
                if ($key === 'source') return ['type' => 'source', 'label' => 'Source'];
                if ($key === 'owner')  return ['type' => 'owner',  'label' => 'Owner'];
                if ($colById->has((int) $key)) {
                    return ['type' => 'data', 'label' => $colById[(int) $key]->name, 'col' => $colById[(int) $key]];
                }
                return null;
            })
            ->filter()
            ->values();

        // ── Filter entries ────────────────────────────────────────────────────
        $entries = $form->entries;

        if ($statusFilter !== null) {
            $entries = $entries->filter(fn ($e) => $e->status_id === (int) $statusFilter)->values();
        }

        // ── Build spreadsheet ─────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(Str::limit($form->name, 31)); // Excel sheet name limit

        // Headers
        $headers = $items->pluck('label')->toArray();
        $sheet->fromArray([$headers], null, 'A1');

        // Style header row: bold + light grey background
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F4F4F5'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Data rows
        $rowIndex = 2;
        foreach ($entries as $entry) {
            $row = $items->map(fn ($item) => match ($item['type']) {
                'status' => $entry->status?->name ?? '',
                'source' => $entry->source ?? '',
                'owner'  => $form->owner?->name ?? '',
                default  => $entry->valueFor($item['col']->id) ?? '',
            })->toArray();

            $sheet->fromArray([$row], null, "A{$rowIndex}");
            $rowIndex++;
        }

        // Auto-size all columns
        $colCount = count($headers);
        for ($i = 1; $i <= $colCount; $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        // ── Stream file ───────────────────────────────────────────────────────
        $filename = Str::slug($form->name) . '_' . now()->format('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}

<?php
namespace App\Exports;

use App\Models\business_master\Bank;
use App\Models\business_master\Broker;
use App\Models\business_master\Cpa;
use App\Models\business_master\Insurer;
use App\Models\business_master\Pincode;
use App\Models\business_master\Rto;
use App\Models\business_master\State;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class BuninessDataExport implements FromQuery, WithHeadings, WithMapping, WithCustomStartCell, WithEvents, WithChunkReading
{
    protected $modelType;
    protected $masterType;

    public function __construct($modelType, $masterType = null)
    {
        $this->modelType = $modelType;
        $this->masterType = $masterType;
    }

    public function query()
    {
        switch ($this->modelType) {
            case 'bank':
                return Bank::where('status', '!=', 3);
            case 'broker':
                return Broker::where('status', '!=', 3);
            case 'state':
                return State::query();
            case 'pincode':
                return Pincode::where('status', '!=', 3);
            case 'rto':
                return Rto::where('status', '!=', 3);
            case 'cpa':
                return Cpa::where('status', '!=', 3);
            case 'insurer':
                return Insurer::where('status', '!=', 3);
            default:
                throw new \InvalidArgumentException('Invalid model type: ' . $this->modelType);
        }
    }

    public function headings(): array
    {
        switch ($this->modelType) {
            case 'bank':
                return ['Name', 'Status', 'Created At', 'Updated At'];
            case 'broker':
                return ['Name', 'Status', 'Created At', 'Updated At'];
            case 'state':
                return ['Zone', 'Name', 'Status', 'Created At', 'Updated At'];
            case 'pincode':
                return ['State Name', 'District Name', 'City Name', 'Area Name', 'Area Pincode', 'Status', 'Created At', 'Updated At'];
            case 'rto':
                return ['Name', 'Code', 'State', 'Status', 'Created At', 'Updated At'];
            case 'cpa':
                return ['Company Name', 'PVT', 'TW', 'GCV', 'MISD', 'PCV', 'Effective Date', 'Status', 'Created At', 'Updated At'];
            case 'insurer':
                return ['Company Name', 'LOB', 'Status', 'Created At', 'Updated At'];
            default:
                throw new \InvalidArgumentException('Invalid model type: ' . $this->modelType);
        }
    }

    public function map($item): array
    {
        switch ($this->modelType) {
            case 'bank':
                return [
                    $item->name,
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->created_at),
                    $this->formatDate($item->updated_at),
                ];
            case 'broker':
                return [
                    $item->name,
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->created_at),
                    $this->formatDate($item->updated_at),
                ];
            case 'state':
                return [
                    $item->zone->name ?? 'N/A',
                    $item->name,
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->created_at),
                    $this->formatDate($item->updated_at),
                ];
            case 'pincode':
                return [
                    $item->state->name ?? 'N/A',
                    $item->district->name ?? 'N/A',
                    $item->city->name ?? 'N/A',
                    $item->area->name ?? 'N/A',
                    $item->pin_code,
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->created_at),
                    $this->formatDate($item->updated_at),
                ];
            case 'rto':
                return [
                    $item->name,
                    $item->code,
                    $item->state->name ?? 'N/A',
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->created_at),
                    $this->formatDate($item->updated_at),
                ];
            case 'cpa':
                return [
                    $item->insurer->name ?? 'N/A',
                    $item->pvt ?? 'N/A',
                    $item->tw ?? 'N/A',
                    $item->gcv ?? 'N/A',
                    $item->misd ?? 'N/A',
                    $item->pcv ?? 'N/A',
                    $item->effective_date ?? 'N/A',
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->created_at),
                    $this->formatDate($item->updated_at),
                ];
            case 'insurer':
                return [
                    $item->name ?? 'N/A',
                    $this->getLobLabels($item),
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->created_at),
                    $this->formatDate($item->updated_at),
                ];
            default:
                throw new \InvalidArgumentException('Invalid model type: ' . $this->modelType);
        }
    }

    private function getLobLabels($item)
    {
        if ($item === null) {
            return 'N/A';
        }

        $lobOptions = [
            'motor' => 'Motor',
            'health' => 'Health',
            'non_motor' => 'Non-Motor',
            'life' => 'Life',
            'travel' => 'Travel',
            'pa' => 'PA',
        ];

        $lobNames = [];
        foreach ($lobOptions as $key => $label) {
            if ($item->$key == 1) {
                $lobNames[] = $label;
            }
        }

        return !empty($lobNames) ? implode(', ', $lobNames) : 'N/A';
    }

    private function formatDate($date): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date instanceof Carbon ? $date->format('d-m-Y H:i:s') : 'N/A';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->freezePane('A2');

                $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => '87CEEB',
                        ],
                    ],
                ]);

                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                foreach (range('A', $highestColumn) as $column) {
                    $sheet->getDelegate()->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

<?php
namespace App\Exports;

use App\Models\Employee\Branch;
use App\Models\Employee\Department;
use App\Models\Employee\RegionalOffice;
use App\Models\Employee\SabBranch;
use App\Models\Employee\EmployeeMaster;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;


class ExportData implements FromQuery, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
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
            case 'department':
                return Department::where('status', '!=', 3);
            case 'regional_office':
                return RegionalOffice::where('status', '!=', 3);
            case 'branch':
                return Branch::where('status', '!=', 3)->with('regionalOffice', 'zone');
            case 'sab_branch':
                return SabBranch::where('status', '!=', 3)->with('branch', 'regionalOffice', 'zone');
            case 'employee_master':
                if ($this->masterType) {
                    return EmployeeMaster::where('status', '!=', 3)
                        ->where('master_type', $this->masterType);
                }
                return EmployeeMaster::where('status', '!=', 3)
                    ->whereIn('master_type', ['Coreline', 'Designation', 'Profile']);
            default:
                throw new \InvalidArgumentException('Invalid model type: ' . $this->modelType);
        }
    }

    public function headings(): array
    {
        switch ($this->modelType) {
            case 'department':
                return [
                    'Name',
                    'Status',
                    'Created At',
                    'Updated At',
                ];
            case 'regional_office':
                return [
                    'Zone',
                    'Name',
                    'Status',
                    'Created At',
                    'Updated At',
                ];
            case 'branch':
                return [
                    'Name',
                    'Zone',
                    'Regional Office',
                    'Address',
                    'Status',
                    'Created At',
                    'Updated At',
                ];
            case 'sab_branch':
                return [
                    'Zone',
                    'Regional Office',
                    'Branch',
                    'Service Location',
                    'Tier',
                    'Status',
                    'Created At',
                    'Updated At',
                ];
            case 'employee_master':
                return [
                    'Name',
                    'Status',
                    'Created At',
                    'Updated At',
                ];
            default:
                throw new \InvalidArgumentException('Invalid model type: ' . $this->modelType);
        }
    }

    public function map($item): array
    {
        switch ($this->modelType) {
            case 'department':
                return [
                    $item->name,
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->add_stamp),
                    $this->formatDate($item->update_stamp),
                ];
            case 'regional_office':
                return [
                    $item->zone->name ?? '',//'N/A'
                    $item->name,
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->add_stamp),
                    $this->formatDate($item->update_stamp),
                ];
            case 'branch':
                return [
                    $item->name,
                    $item->zone->name ?? '',//'N/A'
                    $item->regionalOffice->name ?? '',//'N/A'
                    $item->address,
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->add_stamp),
                    $this->formatDate($item->update_stamp),
                ];
            case 'sab_branch':
                return [
                    $item->zone->name ?? '',//'N/A'
                    $item->regionalOffice->name ?? '',//'N/A'
                    $item->branch->name ?? '',//'N/A'
                    $item->name ?? '',//'N/A'
                    $item->current_tier ?? '',//'N/A'
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->add_stamp),
                    $this->formatDate($item->update_stamp),
                ];
            case 'employee_master':
                return [
                    $item->name,
                    $item->status == 1 ? 'Active' : 'Inactive',
                    $this->formatDate($item->insert_date),
                    $this->formatDate($item->update_stamp),
                ];
            default:
                throw new \InvalidArgumentException('Invalid model type: ' . $this->modelType);
        }
    }

    private function formatDate($date): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return $date instanceof Carbon ? $date->format('d-m-Y H:i:s') : '';//'N/A'
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
                $columnCount = match ($this->modelType) {
                    'department' => 'D',
                    'regional_office' => 'E',
                    'branch' => 'G',
                    'sab_branch' => 'H',
                    'employee_master' => 'D',
                    default => throw new \InvalidArgumentException('Invalid model type: ' . $this->modelType),
                };

                $sheet->getStyle('A1:' . $columnCount . '1')->applyFromArray([
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
                $sheet->getStyle('A1:' . $columnCount . $sheet->getHighestRow())->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // $sheet->getColumnDimension('A')->setWidth(30);
                // $sheet->getColumnDimension('B')->setWidth($this->modelType === 'department' ? 20 : 30);
                // $sheet->getColumnDimension('C')->setWidth(25);
                // $sheet->getColumnDimension('D')->setWidth(30);
                // if ($this->modelType === 'regional_office') {
                //     $sheet->getColumnDimension('D')->setWidth(25);
                //     $sheet->getColumnDimension('E')->setWidth(25);
                // } elseif ($this->modelType === 'branch' || $this->modelType === 'sab_branch') {
                //     $sheet->getColumnDimension('D')->setWidth(30);
                //     $sheet->getColumnDimension('E')->setWidth(30);
                //     $sheet->getColumnDimension('F')->setWidth(25);
                //     $sheet->getColumnDimension('G')->setWidth(30);
                //     if ($this->modelType === 'sab_branch') {
                //         $sheet->getColumnDimension('H')->setWidth(30);
                //     }
                // } elseif ($this->modelType === 'employee_master') {
                //     $sheet->getColumnDimension('D')->setWidth(25);

                // }

            

                $sheet->freezePane('A2');
            },
        ];
    }
}
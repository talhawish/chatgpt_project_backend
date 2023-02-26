<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Jobs\GeneratePost;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\ImportAndGenerate as ModelsImportAndGenerate;

class ImportAndGenerate implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    protected $data;
   

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    public function collection(Collection $rows)
    {
        

    
        foreach ($rows as $row)
        {
        
           
            if(is_int($row['scheduled_date']) && !empty($row['scheduled_date'])) {
                Carbon::instance(Date::excelToDateTimeObject($row['scheduled']))->format('Y-m-d H:i:s');
            }

            if(is_string($row['scheduled_date']) && !empty($row['scheduled_date'])) {

                if (is_string($row['scheduled_at']) && !empty($row['scheduled_at'])) {
                    
                    $scheduled = Carbon::createFromFormat('d/m/Y H:i', $row['scheduled_date'].' '.$row['scheduled_at'])->format('Y-m-d H:i:s');

                } else {
                    $scheduled = Carbon::createFromFormat('d/m/Y', $row['scheduled_date'])->format('Y-m-d H:i:s');
                }


                

            }

            if(empty($row['scheduled_date'])) {
                $scheduled = $this->data['scheduled_at'];
            }


            // $scheduled = empty($row['scheduled']) ? $this->data['scheduled_at'] : Carbon::instance(Date::excelToDateTimeObject($row['scheduled']))->format('Y-m-d H:i:s');
           
          
            $data = [
                
                'keywords' => $row['keywords'], 
                'matchwords' => $row['matchwords'],
                'kind' => $row['kind'],
                'subtitles' => $row['sub_headings'],
                'scheduled_at' => $scheduled,
                'is_generated' => false,
                'website_id' => $this->data['website_id'],
                'categories' => $this->data['categories'],

            ];

            $item = ModelsImportAndGenerate::create($data);

            if($this->data['start']) {
                GeneratePost::dispatch($item);
            }

           
        } 
    }

    // public function headingRow(): int
    // {
    //     return 1;
    // }

    public function startRow(): int
    {
        return 2;
    }

    public function getCsvSettings(): array
{
    return [
        'delimiter' => ","
    ];
}
}

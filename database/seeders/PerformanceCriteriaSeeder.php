<?php

namespace Database\Seeders;

use App\Models\TaskSheet;
use App\Models\JobSheet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PerformanceCriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Performance Criteria for Task Sheets
        $taskSheetCriteria = [
            '1.2.1' => [
                'title' => 'Checking and Testing of Resistor',
                'criteria' => [
                    'Observe and follow safety policies and procedures?',
                    'Use proper personal protective equipment?',
                    'Did I identify different kind of electronic components?',
                    'Follow measuring and correct ranging of Multi-meter?',
                    'I used tools and equipments properly?',
                ]
            ],
            '1.3.1' => [
                'title' => 'Checking and Testing of Capacitor',
                'criteria' => [
                    'Observe and follow safety policies and procedures?',
                    'Use proper personal protective equipment?',
                    'Did I identify different kind of electronic components?',
                    'Follow measuring and correct ranging of Multi-meter?',
                    'I used tools and equipments properly?',
                ]
            ],
            '1.3.2' => [
                'title' => 'Checking and Testing of Diode',
                'criteria' => [
                    'Observe and follow safety policies and procedures?',
                    'Use proper personal protective equipment?',
                    'Did I identify different kind of electronic components?',
                    'Follow measuring and correct ranging of Multi-meter?',
                    'I used tools and equipments properly?',
                ]
            ],
            '1.4.1' => [
                'title' => 'Checking and Testing of Transistor',
                'criteria' => [
                    'Observe and follow safety policies and procedures?',
                    'Use proper personal protective equipment?',
                    'Did I identify different kind of electronic components?',
                    'Follow measuring and correct ranging of Multi-meter?',
                    'I used tools and equipments properly?',
                ]
            ],
            '1.5.1' => [
                'title' => 'Drawing and Testing of Inventory (Power Supply)',
                'criteria' => [
                    'Observe and follow safety policies and procedures?',
                    'Use proper personal protective equipment?',
                    'Did I identify different kind of electronic components?',
                    'Follow measuring and correct ranging of Multi-meter?',
                    'I used tools and equipments properly?',
                    'Schematic diagram interpretation is correct?',
                ]
            ],
            '1.5.2' => [
                'title' => 'Checking and Testing of Inventory (Flip-Flop)',
                'criteria' => [
                    'Observe and follow safety policies and procedures?',
                    'Use proper personal protective equipment?',
                    'Did I identify different kind of electronic components?',
                    'Follow measuring and correct ranging of Multi-meter?',
                    'I used tools and equipments properly?',
                    'Schematic diagram interpretation is correct?',
                ]
            ],
            '1.6.1' => [
                'title' => 'Soldering and De-soldering',
                'criteria' => [
                    'Observe and follow safety policies and procedures?',
                    'Use proper personal protective equipment?',
                    'Did I identify different kind of electronic components?',
                    'Follow measuring and correct ranging of Multi-meter?',
                    'I used tools and equipments properly?',
                    'Soldering technique is correct?',
                    'Solder joints are of good quality (shiny, smooth)?',
                    'No cold solder joints or bridges?',
                ]
            ],
            '1.6.2' => [
                'title' => 'Assembly of Flip-Flop Circuit',
                'criteria' => [
                    'Observe and follow safety policies and procedures?',
                    'Use proper personal protective equipment?',
                    'Did I identify different kind of electronic components?',
                    'Follow measuring and correct ranging of Multi-meter?',
                    'I used tools and equipments properly?',
                    'Soldering technique is correct?',
                    'Solder joints are of good quality?',
                    'Circuit functions properly after assembly?',
                    'Troubleshooting process followed correctly?',
                ]
            ],
        ];

        // Rating scale reference (stored in description)
        $ratingScale = "Rating Scale:\n1 - Poor\n2 - Fair\n3 - Good\n4 - Satisfactory\n5 - Excellent";

        // Update TaskSheets with performance criteria in description
        foreach ($taskSheetCriteria as $taskNumber => $data) {
            $taskSheet = TaskSheet::where('task_number', $taskNumber)->first();
            if ($taskSheet) {
                $criteriaText = "PERFORMANCE CRITERIA - {$data['title']}\n\n{$ratingScale}\n\nCriteria:\n";
                foreach ($data['criteria'] as $i => $criterion) {
                    $criteriaText .= ($i + 1) . ". {$criterion}\n";
                }

                // Append to description if not already there
                if (strpos($taskSheet->description ?? '', 'PERFORMANCE CRITERIA') === false) {
                    $taskSheet->description = ($taskSheet->description ?? '') . "\n\n" . $criteriaText;
                    $taskSheet->save();
                    $this->command->info("Updated Task Sheet {$taskNumber} with performance criteria");
                }
            }
        }

        // Update Job Sheets
        $jobSheet = JobSheet::where('job_number', '1.6.1')->first();
        if ($jobSheet) {
            $jobSheet->performance_criteria = "Performance Criteria Checklist:\n\n{$ratingScale}\n\nCriteria to be evaluated:\n" .
                "• Observe and follow safety policies and procedures?\n" .
                "• Use proper personal protective equipment?\n" .
                "• Did I identify different kind of electronic components?\n" .
                "• Follow measuring and correct ranging of Multi-meter?\n" .
                "• I used tools and equipments properly?\n" .
                "• Soldering technique is correct and joints are good quality?\n" .
                "• Circuit functions properly after assembly?";
            $jobSheet->save();
            $this->command->info("Updated Job Sheet 1.6.1 with performance criteria");
        }
    }
}

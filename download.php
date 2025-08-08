<?php
require 'vendor/autoload.php'; // Ensure you have installed dompdf using `composer require dompdf/dompdf`

use Dompdf\Dompdf;
use Dompdf\Options;

// Read JSON file
$jsonData = file_get_contents("evaluation_data.json");
$evaluations = json_decode($jsonData, true);

// Initialize Dompdf
$options = new Options();
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);

// HTML content for PDF
$html = '<h2 style="text-align:center; color:#470a52;">Project Evaluation Report</h2>';
$html .= '<table border="1" cellpadding="10" cellspacing="0" width="100%" style="border-collapse:collapse; font-size: 12px;">
            <thead>
                <tr style="background-color: #470a52; color: white;">
                    <th>Batch</th>
                    <th>Name</th>
                    <th>Project Title</th>
                    <th>Evaluator</th>
                    <th>PPT [5]</th>
                    <th>Presentation [10]</th>
                    <th>Communication [5]</th>
                    <th>Questionary [5]</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>';

// Loop through each project & team member
foreach ($evaluations as $row) {
    if (isset($row['evaluations'])) {
        foreach ($row['evaluations'] as $eval) {
            $html .= '<tr>
                        <td>' . htmlspecialchars($row['batch']) . '</td>
                        <td>' . htmlspecialchars($row['team_member']) . '</td>
                        <td>' . htmlspecialchars($row['project_title']) . '</td>
                        <td>' . htmlspecialchars($eval['evaluator']) . '</td>
                        <td>' . htmlspecialchars($eval['ppt']) . '</td>
                        <td>' . htmlspecialchars($eval['presentation']) . '</td>
                        <td>' . htmlspecialchars($eval['communication']) . '</td>
                        <td>' . htmlspecialchars($eval['questionary']) . '</td>
                        <td><strong>' . htmlspecialchars($eval['total']) . '</strong></td>
                      </tr>';
        }
    }
}

$html .= '</tbody></table>';

// Load HTML into Dompdf
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Output PDF
$dompdf->stream("Project_Evaluation_Report.pdf", ["Attachment" => 1]);
?>

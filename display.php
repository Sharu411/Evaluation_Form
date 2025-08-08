<?php
$filename = 'evaluation_data.json';

// Load data
$data = file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Evaluation Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .header {
            background-color: #470a52;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table thead {
            background-color: #470a52;
            color: white;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f8e8f0;
        }
        .table tbody tr:hover {
            background-color: #E1306C;
            color: white;
        }
        .btn-print {
            background-color: #E1306C;
            border: none;
        }
        .btn-print:hover {
            background-color: #c02758;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <h2 class="header">Project Evaluation Report</h2>

        <div class="table-container mt-4">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Batch</th>
                        <th>Name</th>
                        <th>Project Title</th>
                        <th>Evaluator</th>
                        <th>PPT (5)</th>
                        <th>Presentation (10)</th>
                        <th>Communication (5)</th>
                        <th>Questionary (5)</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($data as $entry) {
                        if (isset($entry['evaluations'])) {
                            foreach ($entry['evaluations'] as $eval) {
                                echo "<tr>
                                    <td>{$entry['batch']}</td>
                                    <td>{$entry['team_member']}</td>
                                    <td>{$entry['project_title']}</td>
                                    <td>{$eval['evaluator']}</td>
                                    <td>{$eval['ppt']}</td>
                                    <td>{$eval['presentation']}</td>
                                    <td>{$eval['communication']}</td>
                                    <td>{$eval['questionary']}</td>
                                    <td>{$eval['total']}</td>
                                </tr>";
                            }
                        } elseif (isset($entry['evaluator'])) {
                            // Support for old format
                            echo "<tr>
                                <td>{$entry['batch']}</td>
                                <td>{$entry['team_member']}</td>
                                <td>{$entry['project_title']}</td>
                                <td>{$entry['evaluator']}</td>
                                <td>{$entry['ppt']}</td>
                                <td>{$entry['presentation']}</td>
                                <td>{$entry['communication']}</td>
                                <td>{$entry['questionary']}</td>
                                <td>{$entry['total']}</td>
                            </tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-3">
        <a href="download.php" class="btn btn-action px-4 py-2" style="background-color: #E1306C;color:white;">Download Report</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

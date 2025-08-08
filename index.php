<?php
require 'vendor/autoload.php'; // Ensure this line is present
use PhpOffice\PhpSpreadsheet\IOFactory;

// Load the Excel file
$excelFile = "projects.xlsx";
$spreadsheet = IOFactory::load($excelFile);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

// Extract projects, batches & team members
$projects = [];
foreach ($rows as $index => $row) {
    if ($index == 0) continue; // Skip header
    $projects[$row[2]] = ["batch" => $row[0], "team_members" => explode(", ", $row[4])]; // Map Title to Batch & Members
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = file_exists("evaluation_data.json") ? json_decode(file_get_contents("evaluation_data.json"), true) : [];

    $evaluator = $_POST["evaluator"];
    $projectTitle = $_POST["project_title"];
    $batch = $_POST["batch"];
    $teamMembers = $_POST["team_members"];

    foreach ($teamMembers as $member) {
        $new_evaluation = [
            "evaluator" => $evaluator,
            "ppt" => (int)$_POST["ppt"][$member],
            "presentation" => (int)$_POST["presentation"][$member],
            "communication" => (int)$_POST["communication"][$member],
            "questionary" => (int)$_POST["questionary"][$member],
            "total" => (int)$_POST["ppt"][$member] + (int)$_POST["presentation"][$member] +
                       (int)$_POST["communication"][$member] + (int)$_POST["questionary"][$member]
        ];

        $found = false;
        foreach ($data as &$entry) {
            if ($entry["batch"] == $batch && $entry["team_member"] == $member && $entry["project_title"] == $projectTitle) {
                if (!isset($entry["evaluations"])) {
                    // Convert old format to evaluations array
                    $entry["evaluations"] = [[
                        "evaluator" => $entry["evaluator"],
                        "ppt" => $entry["ppt"],
                        "presentation" => $entry["presentation"],
                        "communication" => $entry["communication"],
                        "questionary" => $entry["questionary"],
                        "total" => $entry["total"]
                    ]];
                    unset($entry["evaluator"], $entry["ppt"], $entry["presentation"], $entry["communication"], $entry["questionary"], $entry["total"]);
                }

                // Check if this evaluator has already evaluated
                $evaluatorExists = false;
                foreach ($entry["evaluations"] as &$eval) {
                    if ($eval["evaluator"] == $evaluator) {
                        // Update existing evaluation
                        $eval["ppt"] = $new_evaluation["ppt"];
                        $eval["presentation"] = $new_evaluation["presentation"];
                        $eval["communication"] = $new_evaluation["communication"];
                        $eval["questionary"] = $new_evaluation["questionary"];
                        $eval["total"] = $new_evaluation["total"];
                        $evaluatorExists = true;
                        break;
                    }
                }

                // If a new evaluator, add a new evaluation entry
                if (!$evaluatorExists) {
                    $entry["evaluations"][] = $new_evaluation;
                }

                $found = true;
                break;
            }
        }

        // If no record exists for this team member, create a new one
        if (!$found) {
            $data[] = [
                "batch" => $batch,
                "team_member" => $member,
                "project_title" => $projectTitle,
                "evaluations" => [$new_evaluation]
            ];
        }
    }

    file_put_contents("evaluation_data.json", json_encode($data, JSON_PRETTY_PRINT));
    echo "Evaluation submitted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Evaluation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: white; color: white; }
        .container { background: #470a52; padding: 20px; border-radius: 10px; color: white; }
        .btn-primary { background-color: #E1306C; border-color: #E1306C; }
        .btn-primary:hover { background-color: #E1306C;}
        .form-label { font-weight: bold; }
        table { background-color: white; color: black; }
        th { background-color: #E1306C; color: white; }
    </style>
    <script>
        function updateTeamMembers() {
            let project = document.getElementById("project_title").value;
            let teamData = JSON.parse('<?php echo json_encode($projects); ?>');
            let batchField = document.getElementById("batch");
            let membersTable = document.getElementById("team_members_table");
            
            membersTable.innerHTML = "<tr><th>Name</th><th>PPT[5]</th><th>Presentation[10]</th><th>Communication[5]</th><th>Questionnaire[5]</th></tr>";
            if (project in teamData) {
                batchField.value = teamData[project]['batch'];
                let members = teamData[project]['team_members'];
                members.forEach(member => {
                    membersTable.innerHTML += `
                        <tr>
                            <td>${member}</td>
                            <input type="hidden" name="team_members[]" value="${member}">
                            <td><input type="number" class="form-control" name="ppt[${member}]" min="0" max="5" required></td>
                            <td><input type="number" class="form-control" name="presentation[${member}]" min="0" max="10" required></td>
                            <td><input type="number" class="form-control" name="communication[${member}]" min="0" max="5" required></td>
                            <td><input type="number" class="form-control" name="questionary[${member}]" min="0" max="5" required></td>
                        </tr>
                    `;
                });
            }
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Project Evaluation Form</h2>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Project Title:</label>
                <select class="form-select" name="project_title" id="project_title" onchange="updateTeamMembers()" required>
                    <option value="">Select Project</option>
                    <?php foreach ($projects as $title => $info): ?>
                        <option value="<?= htmlspecialchars($title) ?>"><?= htmlspecialchars($title) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Batch No:</label>
                <input type="text" class="form-control" id="batch" name="batch" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Evaluator</label>
                <select class="form-select" name="evaluator" required>
                    <option value="">Select Evaluator</option>
                    <option value="Dr.K.M.Alaaudeen">Dr.K.M.Alaaudeen</option>
                    <option value="Dr.Felcia Jerlin">Dr.Felcia Jerlin</option>
                    <option value="Ms.Abarna">Ms.Abarna</option>
                    <option value="Mrs.Joy Suganthy Bai">Mrs.Joy Suganthy Bai</option>
                    <option value="Mrs.Revathy">Mrs.Revathy</option>
                    <option value="Mrs.Subashree Kasi Thangam">Mrs.Subashree Kasi Thangam</option>
                    <option value="Ms.Rebecca Fernando">Ms.Rebecca Fernando</option>
                </select>
            </div>
            <table class="table table-bordered" id="team_members_table"></table>
            
            <button type="submit" class="btn btn-primary w-100">Submit Evaluation</button>
        </form>
    </div>
    <div class="text-center mt-4">
    <a href="display.php" class="btn btn-warning btn-lg">View Evaluations marks</a>
</div>

</body>
</html>
<?php
session_start();

$performance = [];
$answerSheet = "ABCCB";


function calcScore($ans, $answerSheet) {
    $score = 0;
    $len = min(strlen($ans), strlen($answerSheet));
    for ($i = 0; $i < $len; $i++) {
        if ($ans[$i] === $answerSheet[$i]) {
            $score++;
        }
    }
    return $score;
}


function readStudents($fileName) {
    $students = [];
    if (!file_exists($fileName)) {
        return $students; 
    }
    $file = fopen($fileName, "r");
    if ($file) {
        while (($line = fgets($file)) !== false) {
            $line = trim($line);
            if ($line === "") continue;
            $parts = explode(",", $line);
            if (count($parts) >= 4) {
                $students[] = [
                    'id' => trim($parts[0]),
                    'name' => trim($parts[1]),
                    'answers' => trim($parts[2]),
                    'score' => (int) trim($parts[3])
                ];
            }
        }
        fclose($file);
    }
    return $students;
}


function writeStudents($fileName, $students) {
    $file = fopen($fileName, "w");
    foreach ($students as $std) {
        fwrite($file, "{$std['id']},{$std['name']},{$std['answers']},{$std['score']}\n");
    }
    fclose($file);
}


function displayStudent($student) {
    echo "<p>Αρ.Μητρωου: " . htmlspecialchars($student['id']) . "<br>Ονομα: " . htmlspecialchars($student['name']) . "<br>Απαντησεις: " . htmlspecialchars($student['answers']) . "<br>Βαθμος: " . $student['score'] . "</p>";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answers'])) {
    $name = trim($_POST['name']);
    $id = trim($_POST['id']);
    $answers = strtoupper(trim($_POST['answers']));
    $score = calcScore($answers, $answerSheet);
    
    $student = [
        'id' => $id,
        'name' => $name,
        'answers' => $answers,
        'score' => $score
    ];

    
    $performance[] = [$name, $score];

    
    $students = readStudents("Students.txt");
    $students[] = $student;
    writeStudents("Students.txt", $students);

    
    $existingPerformance = [];
    if (file_exists("Performance.txt")) {
        $lines = file("Performance.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            list($n, $s) = explode(" ", trim($line));
            $existingPerformance[] = [$n, (int)$s];
        }
    }
    $existingPerformance[] = [$name, $score];

   
    usort($existingPerformance, function($a, $b) {
        return $a[1] - $b[1];
    });

    
    $perfFile = fopen("Performance.txt", "w");
    foreach ($existingPerformance as $p) {
        fwrite($perfFile, "{$p[0]} {$p[1]}\n");
    }
    fclose($perfFile);
}


$selectedStudent = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['index'])) {
    $index = intval($_GET['index']);
    $students = readStudents("Students.txt");
    if (isset($students[$index])) {
        $selectedStudent = $students[$index];
    }
}

$studentsForLowest = readStudents("Students.txt");
usort($studentsForLowest, function($a, $b) {
    return $a['score'] - $b['score'];
});
$lowestStudents = array_slice($studentsForLowest, 0, 2);

if (count($studentsForLowest)>2){
	for ($i=2;$i<count($studentsForLowest);$i++){
		if($studentsForLower[$i]["score"] === $studentsForLower[2]["score"]){
			array_push($lowestStudents,$studentsForLower[$i]);
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Εφαρμογή 1 PHP</title>
<meta charset="UTF-8">
</head>
<body>

<h2>Στοιχεια Φοιτητη</h2>
<form method="post">
Ονομα: <input type="text" name="name" required><br>
Αρ.Μητρωου: <input type="text" name="id" required><br>
Απαντησεις: <input type="text" name="answers" maxlength="5" required><br>
<input type="submit" value="Submit">
</form>

<h2>Δες φοιτητη μεσου index</h2>
<form method="get">
Index του μαθητη: <input type="text" name="index" required><br>
<input type="submit" value="Δειξε μαθητη">
</form>

<?php
if ($selectedStudent !== null) {
    echo "<h3>Πληροφοριες επιλεγμενου φοιτητη:</h3>";
    displayStudent($selectedStudent);
}
?>

<h2>Χειροτερες αποδωσεις</h2>
<?php
if (count($lowestStudents) > 0) {
    foreach ($lowestStudents as $std) {
        displayStudent($std);
    }
} else {
    echo "<p>Δεν υπαρχουν δεδομενα του φοιτητη.</p>";
}

?>

</body>
</html>

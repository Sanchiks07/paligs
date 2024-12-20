<?php

echo "<link rel='stylesheet' href='children-styles.css'>";
echo "<link rel='stylesheet' href='snowfall.css'>";

require "Database.php";
$config = require ("config.php");

$db = new Database($config["database"]);
$children = $db->query("SELECT * FROM children")->fetchAll();
$letters = $db->query("SELECT * FROM letters")->fetchAll();
$gifts = $db->query("SELECT * FROM gifts")->fetchAll();
$grades = $db->query("SELECT student_id, grade FROM grades")->fetchAll();

$gift_names = array_column($gifts, "name");

$average_grades = [];
foreach ($grades as $grade) {
    $student_id = $grade['student_id'];
    $grade_value = $grade['grade'];

    if (!isset($average_grades[$student_id])) {
        $average_grades[$student_id] = ['total' => 0, 'count' => 0];
    }

    $average_grades[$student_id]['total'] += $grade_value;
    $average_grades[$student_id]['count']++;
}

// Aprēķina vidējo visiem bērniem
foreach ($average_grades as $student_id => $data) {
    $average_grades[$student_id]['average'] = $data['total'] / $data['count'];
}

echo "<h1>🎅🏻Bērnu vēstules🎅🏻</h1>";
echo "<div class='snowfall'></div>";
echo "<div class='card-container'>";

function getGiftColor($average) {
    return $average < 5 ? 'red' : 'green';
}

// Izvada vidējo atzīmi
foreach ($children as $child) {
    echo "<div class='card'>";
    echo "<h3>🎄" . $child["firstname"] . " " . $child["middlename"] . " " . $child["surname"] . " - " . $child["age"] . "🎄</h3>";

    $average = isset($average_grades[$child['id']]) ? $average_grades[$child['id']]['average'] : 0;
    echo "<p>Vidējā atzīme: " . number_format($average, 2) . "</p>";

    $child_letters = [];
    foreach ($letters as $letter) {
        if ($letter["sender_id"] == $child["id"]) {
            $child_letters[] = $letter;
        }
    }

    if (!empty($child_letters)) {
        echo "✉️ Vēstule:";
        foreach ($child_letters as $letter) {
            $highlighted_text = $letter['letter_text'];
            $wishes = [];

            foreach ($gift_names as $gift) {
                // Nosauktās dāvanas vēstulē pataisa bold
                $highlighted_text = preg_replace("/\b" . preg_quote($gift, '/') . "\b/i", "<strong style='color: " . getGiftColor($average) . ";'>" . $gift . "</strong>", $highlighted_text);

                if (stripos($letter['letter_text'], $gift) !== false) {
                    $wishes[] = htmlspecialchars($gift);
                }
            }
    
            echo "<div class='letter-card'>";
            echo "<p>" . nl2br($highlighted_text) . "</p>";
            echo "</div>";

            if (!empty($wishes)) {
                echo "<br>🧸 Vēlmju saraksts:";
                echo "<ul>";
                foreach ($wishes as $wish) {
                    echo "<li style='color: " . getGiftColor($average) . ";'>$wish</li>";
                }
                echo "</ul>";
            }
        }
    }
    
    echo "</div>";
}

echo "</div>";

echo "<div class='link'>";
echo "<a href='gifts.php'>Skatīt Dāvanu Sarakstu</a>";
echo "</div>";
?>

<script src='snowfall.js'></script>
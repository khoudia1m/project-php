<?php


$jsonFile = 'tasks.json';

if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([]));
}

function loadTasks($file) {
    return json_decode(file_get_contents($file), true);
}

function saveTasks($file, $tasks) {
    file_put_contents($file, json_encode(array_values($tasks)));
}

$tasks = loadTasks($jsonFile);

if (isset($_POST['add_task'])) {
    $newTask = [
        'id' => uniqid(),
        'titre' => htmlspecialchars($_POST['titre']),
        'description' => htmlspecialchars($_POST['description']),
        'priorite' => $_POST['priorite'],
        'statut' => 'à faire',
        'date_creation' => date('Y-m-d H:i'),
        'date_limite' => $_POST['date_limite']
    ];
    $tasks[] = $newTask;
    saveTasks($jsonFile, $tasks);
    header('Location: index.php');
    exit;
}

if (isset($_GET['action'])) {
    $id = $_GET['id'] ?? null;
    
    if ($_GET['action'] == 'update_status' && isset($_GET['status'])) {
        foreach ($tasks as &$task) {
            if ($task['id'] == $id) {
                $task['statut'] = $_GET['status'];
                break;
            }
        }
        saveTasks($jsonFile, $tasks);
    }

    if ($_GET['action'] == 'delete') {
        $tasks = array_filter($tasks, fn($t) => $t['id'] != $id);
        saveTasks($jsonFile, $tasks);
    }
    
    header('Location: index.php');
    exit;
}

$search = $_GET['search'] ?? '';
$filter_statut = $_GET['filter_statut'] ?? '';
$filter_priorite = $_GET['filter_priorite'] ?? '';

$filteredTasks = array_filter($tasks, function($task) use ($search, $filter_statut, $filter_priorite) {
    $matchSearch = empty($search) || stripos($task['titre'], $search) !== false || stripos($task['description'], $search) !== false;
    $matchStatut = empty($filter_statut) || $task['statut'] == $filter_statut;
    $matchPriorite = empty($filter_priorite) || $task['priorite'] == $filter_priorite;
    return $matchSearch && $matchStatut && $matchPriorite;
});

$totalTasks = count($tasks);
$completedTasks = count(array_filter($tasks, fn($t) => $t['statut'] == 'terminée'));
$percentCompleted = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
$today = date('Y-m-d');
$lateTasks = count(array_filter($tasks, fn($t) => $t['statut'] != 'terminée' && $t['date_limite'] < $today));
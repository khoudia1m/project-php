<?php

$jsonFile = 'tasks.json';

if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([]));
}

$tasks = json_decode(file_get_contents($jsonFile), true);


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
    file_put_contents($jsonFile, json_encode($tasks));
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'update_status') {
    $id = $_GET['id'];
    $newStatus = $_GET['status'];
    foreach ($tasks as &$task) {
        if ($task['id'] == $id) {
            $task['statut'] = $newStatus;
            break;
        }
    }
    file_put_contents($jsonFile, json_encode($tasks));
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    $tasks = array_filter($tasks, function($t) use ($id) {
        return $t['id'] != $id;
    });
    file_put_contents($jsonFile, json_encode(array_values($tasks)));
    header('Location: index.php');
    exit;
}

$search = $_GET['search'] ?? '';
$filter_statut = $_GET['filter_statut'] ?? '';
$filter_priorite = $_GET['filter_priorite'] ?? '';

$filteredTasks = array_filter($tasks, function($task) use ($search, $filter_statut, $filter_priorite) {
    $matchSearch = empty($search) || 
                   stripos($task['titre'], $search) !== false || 
                   stripos($task['description'], $search) !== false;
    $matchStatut = empty($filter_statut) || $task['statut'] == $filter_statut;
    $matchPriorite = empty($filter_priorite) || $task['priorite'] == $filter_priorite;
    
    return $matchSearch && $matchStatut && $matchPriorite;
});

$totalTasks = count($tasks);
$completedTasks = count(array_filter($tasks, fn($t) => $t['statut'] == 'terminée'));
$percentCompleted = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

$today = date('Y-m-d');
$lateTasks = count(array_filter($tasks, function($t) use ($today) {
    return $t['statut'] != 'terminée' && $t['date_limite'] < $today;
}));

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini Projet PHP - Gestion de Tâches</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">

    <nav class="bg-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold uppercase tracking-wider">L2 IAGE-GDA 2026 | TaskManager</h1>
            <div class="text-sm">Ndeye Khoudia MBACKE</div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm uppercase">Total Tâches</p>
                <p class="text-2xl font-bold"><?= $totalTasks ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                <p class="text-gray-500 text-sm uppercase">Terminées</p>
                <p class="text-2xl font-bold"><?= $completedTasks ?> (<?= $percentCompleted ?>%)</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
                <p class="text-gray-500 text-sm uppercase">En Retard</p>
                <p class="text-2xl font-bold"><?= $lateTasks ?></p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <p class="text-gray-500 text-sm uppercase">Date du jour</p>
                <p class="text-lg font-semibold"><?= date('d/m/Y') ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="bg-white p-6 rounded-xl shadow-md h-fit">
                <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2">
                    <i class="fa-solid fa-plus-circle mr-2"></i>Nouvelle Tâche
                </h2>
                <form action="index.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Titre</Clabel>
                        <input type="text" name="titre" required class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" required class="w-full mt-1 p-2 border rounded-md focus:ring-2 focus:ring-blue-400 outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Priorité</label>
                        <select name="priorite" class="w-full mt-1 p-2 border rounded-md bg-white">
                            <option value="basse">Basse</option>
                            <option value="moyenne">Moyenne</option>
                            <option value="haute">Haute</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date Limite</label>
                        <input type="date" name="date_limite" required class="w-full mt-1 p-2 border rounded-md bg-white">
                    </div>
                    <button type="submit" name="add_task" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
                        Enregistrer la tâche
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2">
                
              
                <div class="bg-white p-4 rounded-xl shadow-sm mb-6 flex flex-wrap gap-4 items-end">
                    <form action="index.php" method="GET" class="flex flex-wrap gap-4 w-full">
                        <div class="flex-1 min-w-[200px]">
                            <label class="text-xs font-bold text-gray-400 uppercase">Recherche</label>
                            <input type="text" name="search" value="<?= $search ?>" placeholder="Mot-clé..." class="w-full p-2 border rounded-md">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Statut</label>
                            <select name="filter_statut" class="w-full p-2 border rounded-md">
                                <option value="">Tous</option>
                                <option value="à faire" <?= $filter_statut == 'à faire' ? 'selected' : '' ?>>À faire</option>
                                <option value="en cours" <?= $filter_statut == 'en cours' ? 'selected' : '' ?>>En cours</option>
                                <option value="terminée" <?= $filter_statut == 'terminée' ? 'selected' : '' ?>>Terminée</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-400 uppercase">Priorité</label>
                            <select name="filter_priorite" class="w-full p-2 border rounded-md">
                                <option value="">Toutes</option>
                                <option value="basse" <?= $filter_priorite == 'basse' ? 'selected' : '' ?>>Basse</option>
                                <option value="moyenne" <?= $filter_priorite == 'moyenne' ? 'selected' : '' ?>>Moyenne</option>
                                <option value="haute" <?= $filter_priorite == 'haute' ? 'selected' : '' ?>>Haute</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-black">Filtrer</button>
                            <a href="index.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 flex items-center">Réinitialiser</a>
                        </div>
                    </form>
                </div>

              
                <div class="space-y-4">
                    <?php if (empty($filteredTasks)): ?>
                        <div class="bg-white p-8 text-center rounded-xl shadow-sm text-gray-500 italic">
                            Aucune tâche trouvée.
                        </div>
                    <?php endif; ?>

                    <?php foreach ($filteredTasks as $task): 
                        $isLate = ($task['statut'] != 'terminée' && $task['date_limite'] < $today);
                        $prioColor = [
                            'basse' => 'bg-green-100 text-green-700',
                            'moyenne' => 'bg-yellow-100 text-yellow-700',
                            'haute' => 'bg-red-100 text-red-700'
                        ];
                        $statutColor = [
                            'à faire' => 'bg-gray-100 text-gray-600',
                            'en cours' => 'bg-blue-100 text-blue-600',
                            'terminée' => 'bg-emerald-100 text-emerald-600'
                        ];
                    ?>
                        <div class="bg-white p-5 rounded-xl shadow-sm border <?= $isLate ? 'border-red-500 bg-red-50' : 'border-gray-100' ?> transition hover:shadow-md">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                                        <?= $task['titre'] ?>
                                        <?php if ($isLate): ?>
                                            <span class="ml-2 bg-red-600 text-white text-[10px] px-2 py-1 rounded uppercase animate-pulse">En Retard</span>
                                        <?php endif; ?>
                                    </h3>
                                    <p class="text-gray-600 text-sm mt-1"><?= $task['description'] ?></p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?= $prioColor[$task['priorite']] ?>">
                                        <?= $task['priorite'] ?>
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?= $statutColor[$task['statut']] ?>">
                                        <?= $task['statut'] ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap justify-between items-center pt-4 border-t border-gray-50 mt-4 text-xs">
                                <div class="flex gap-4 text-gray-500">
                                    <span><i class="fa-regular fa-calendar-plus mr-1"></i> Créé : <?= $task['date_creation'] ?></span>
                                    <span class="<?= $isLate ? 'text-red-600 font-bold' : '' ?>">
                                        <i class="fa-regular fa-clock mr-1"></i> Limite : <?= date('d/m/Y', strtotime($task['date_limite'])) ?>
                                    </span>
                                </div>
                                <div class="flex gap-2">
                                 
                                    <?php if ($task['statut'] != 'à faire'): ?>
                                        <a href="index.php?action=update_status&id=<?= $task['id'] ?>&status=à faire" class="text-gray-400 hover:text-gray-600 p-1" title="Mettre à faire"><i class="fa-solid fa-circle-notch"></i></a>
                                    <?php endif; ?>
                                    
                                    <?php if ($task['statut'] != 'en cours'): ?>
                                        <a href="index.php?action=update_status&id=<?= $task['id'] ?>&status=en cours" class="text-blue-400 hover:text-blue-600 p-1" title="Démarrer"><i class="fa-solid fa-play"></i></a>
                                    <?php endif; ?>
                                    
                                    <?php if ($task['statut'] != 'terminée'): ?>
                                        <a href="index.php?action=update_status&id=<?= $task['id'] ?>&status=terminée" class="text-green-400 hover:text-green-600 p-1" title="Terminer"><i class="fa-solid fa-check-double"></i></a>
                                    <?php endif; ?>

                                    <a href="index.php?action=delete&id=<?= $task['id'] ?>" onclick="return confirm('Supprimer cette tâche ?')" class="text-red-400 hover:text-red-600 p-1 ml-4" title="Supprimer">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-12 p-8 text-center text-gray-400 text-sm">
        &copy; 2026 - Mini Projet PHP - L2 IAGE-GDA - Ndeye Khoudia MBACKE
    </footer>

</body>
</html>
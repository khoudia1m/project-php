<?php 

if (file_exists('functions.php')) {
    require_once 'functions.php';
} else {
    die("Erreur critique : Le fichier functions.php est introuvable.");
}


if (file_exists('header.php')) {
    include 'header.php';
} else {
    echo "Attention : header.php introuvable.<br>";
}
?>


<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
    <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-indigo-500">
        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Tâches</p>
        <p class="text-3xl font-black text-slate-800"><?= isset($totalTasks) ? $totalTasks : 0 ?></p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-emerald-500">
        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Terminées</p>
        <p class="text-3xl font-black text-slate-800"><?= isset($completedTasks) ? $completedTasks : 0 ?></p>
        <p class="text-xs text-emerald-600 mt-1 font-semibold"><?= isset($percentCompleted) ? $percentCompleted : 0 ?>% de réussite</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-rose-500">
        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">En Retard</p>
        <p class="text-3xl font-black text-slate-800"><?= isset($lateTasks) ? $lateTasks : 0 ?></p>
        <p class="text-xs text-rose-500 mt-1 font-semibold italic">Action urgente</p>
    </div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-amber-500">
        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Aujourd'hui</p>
        <p class="text-xl font-bold text-slate-700 mt-1"><?= date('d/m/Y') ?></p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
    
    <div class="lg:col-span-4">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
            <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-indigo-600"></i> Nouvelle Tâche
            </h2>
            <form action="index.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Titre</label>
                    <input type="text" name="titre" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Description</label>
                    <textarea name="description" rows="3" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Priorité</label>
                        <select name="priorite" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
                            <option value="basse">Basse</option>
                            <option value="moyenne" selected>Moyenne</option>
                            <option value="haute">Haute</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Date Limite</label>
                        <input type="date" name="date_limite" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl outline-none">
                    </div>
                </div>
                <button type="submit" name="add_task" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg transition duration-200 active:scale-95">
                    Enregistrer la tâche
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-8">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-8">
            <form action="index.php" method="GET" class="flex flex-wrap gap-4 items-center">
                <div class="flex-grow">
                    <input type="text" name="search" value="<?= isset($search) ? $search : '' ?>" placeholder="Chercher une tâche..." class="w-full p-3 border border-slate-200 rounded-xl text-sm">
                </div>
                <select name="filter_statut" class="p-3 border border-slate-200 rounded-xl text-sm min-w-[150px]">
                    <option value="">Tous les statuts</option>
                    <option value="à faire" <?= (isset($filter_statut) && $filter_statut == 'à faire') ? 'selected' : '' ?>>À faire</option>
                    <option value="en cours" <?= (isset($filter_statut) && $filter_statut == 'en cours') ? 'selected' : '' ?>>En cours</option>
                    <option value="terminée" <?= (isset($filter_statut) && $filter_statut == 'terminée') ? 'selected' : '' ?>>Terminée</option>
                </select>
                <button type="submit" class="bg-slate-800 text-white p-3 rounded-xl hover:bg-slate-900 transition">
                    <i class="fa-solid fa-magnifying-glass px-2"></i>
                </button>
            </form>
        </div>

        <div class="space-y-4">
            <?php if (isset($filteredTasks) && count($filteredTasks) > 0): ?>
                <?php foreach ($filteredTasks as $task): 
                    $isLate = ($task['statut'] != 'terminée' && $task['date_limite'] < $today);
                    $prioColors = [
                        'basse' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                        'moyenne' => 'bg-amber-50 text-amber-700 border-amber-100',
                        'haute' => 'bg-rose-50 text-rose-700 border-rose-100'
                    ];
                ?>
                    <div class="task-card bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative <?= $isLate ? 'ring-2 ring-rose-500 ring-opacity-50' : '' ?>">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex-grow">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-bold text-slate-800"><?= $task['titre'] ?></h3>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full border <?= $prioColors[$task['priorite']] ?>">
                                        <?= $task['priorite'] ?>
                                    </span>
                                </div>
                                <p class="text-slate-500 text-sm mb-4"><?= $task['description'] ?></p>
                                <div class="flex flex-wrap gap-4 text-[11px] font-bold uppercase text-slate-400">
                                    <span><i class="fa-regular fa-clock mr-1"></i> Échéance : <span class="<?= $isLate ? 'text-rose-600 font-bold' : 'text-slate-600' ?>"><?= date('d/m/Y', strtotime($task['date_limite'])) ?></span></span>
                                    <span><i class="fa-solid fa-tag mr-1"></i> Statut : <span class="text-indigo-600"><?= $task['statut'] ?></span></span>
                                </div>
                            </div>
                            
                            <div class="flex flex-col gap-2">
                                <div class="flex gap-2">
                                    <a href="index.php?action=update_status&id=<?= $task['id'] ?>&status=en cours" class="p-2 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition" title="En cours"><i class="fa-solid fa-play"></i></a>
                                    <a href="index.php?action=update_status&id=<?= $task['id'] ?>&status=terminée" class="p-2 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition" title="Terminée"><i class="fa-solid fa-check"></i></a>
                                </div>
                                <a href="index.php?action=delete&id=<?= $task['id'] ?>" onclick="return confirm('Supprimer cette tâche ?')" class="p-2 bg-rose-50 text-rose-600 rounded-lg hover:bg-rose-600 hover:text-white transition text-center" title="Supprimer">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </div>
                        </div>

                        <?php if ($isLate): ?>
                            <div class="absolute -top-3 -right-3 bg-rose-600 text-white text-[10px] font-black px-3 py-1 rounded-lg shadow-lg uppercase tracking-tighter">
                                Retard
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white p-10 rounded-2xl border border-dashed border-slate-200 text-center text-slate-400">
                    <i class="fa-solid fa-clipboard-list text-4xl mb-3 block opacity-20"></i>
                    Aucune tâche trouvée.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
if (file_exists('footer.php')) {
    include 'footer.php';
}
?>
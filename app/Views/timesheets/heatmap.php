<?php require APPROOT . '/Views/inc/header.php'; ?>

<style>
    .heatmap-container {
        overflow-x: auto;
        padding: 20px 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .heatmap {
        display: inline-grid;
        grid-template-columns: repeat(auto-fill, 12px);
        grid-auto-flow: column;
        grid-template-rows: repeat(7, 12px);
        gap: 3px;
        padding: 10px;
    }
    .heatmap-day {
        width: 12px;
        height: 12px;
        border-radius: 2px;
        background-color: #ebedf0;
        cursor: pointer;
        position: relative;
    }
    /* GitHub typical colors */
    .level-0 { background-color: #ebedf0; }
    .level-1 { background-color: #9be9a8; }
    .level-2 { background-color: #40c463; }
    .level-3 { background-color: #30a14e; }
    .level-4 { background-color: #216e39; }

    .heatmap-labels-y {
        display: grid;
        grid-template-rows: repeat(7, 12px);
        gap: 3px;
        padding-right: 8px;
        font-size: 9px;
        color: #767676;
        text-align: right;
    }
    .heatmap-wrapper {
        display: flex;
        align-items: flex-start;
        padding: 20px;
    }
    .legend {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: #767676;
        margin-top: 10px;
        padding: 0 20px 20px;
    }
    .legend-box {
        width: 10px;
        height: 10px;
        border-radius: 2px;
    }
    .tooltip-inner {
        font-size: 11px;
    }
</style>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Performance Semestrielle</h2>
                <div class="text-muted mt-1">
                    Visualisation type "Contributions GitHub" des 6 derniers mois
                </div>
            </div>
            <div class="col-auto ms-auto">
                <a href="<?= URLROOT ?>/timesheets" class="btn btn-outline-primary">
                    Retour au Timesheet hebdo
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="heatmap-wrapper">
                <div class="heatmap-labels-y">
                    <span></span><span>Lun</span><span></span><span>Mer</span><span></span><span>Ven</span><span></span>
                </div>
                <div class="heatmap">
                    <?php
                    $current = clone $data['start_date'];
                    // Adjust to start on Monday if necessary to align grid correctly
                    $dayOfWeek = (int)$current->format('N');
                    if ($dayOfWeek > 1) {
                        for ($i = 1; $i < $dayOfWeek; $i++) {
                            echo '<div class="heatmap-day" style="visibility:hidden"></div>';
                        }
                    }

                    while ($current <= $data['end_date']) {
                        $date_str = $current->format('Y-m-d');
                        $hours = isset($data['daily_stats'][$date_str]) ? $data['daily_stats'][$date_str] : 0;
                        
                        $level = 0;
                        if ($hours > 0) $level = 1;
                        if ($hours >= 4) $level = 2;
                        if ($hours >= 7) $level = 3;
                        if ($hours >= 9) $level = 4;

                        $title = $current->format('d M Y') . ' : ' . number_format($hours, 1) . 'h';
                        
                        echo "<div class='heatmap-day level-$level' 
                                   data-bs-toggle='tooltip' 
                                   data-bs-placement='top' 
                                   title='$title'></div>";
                        
                        $current->modify('+1 day');
                    }
                    ?>
                </div>
            </div>
            <div class="legend">
                <span>Moins</span>
                <div class="legend-box level-0"></div>
                <div class="legend-box level-1"></div>
                <div class="legend-box level-2"></div>
                <div class="legend-box level-3"></div>
                <div class="legend-box level-4"></div>
                <span>Plus</span>
            </div>
        </div>

        <div class="row row-cards mt-4">
            <div class="col-md-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><circle cx="12" cy="12" r="9" /><polyline points="12 7 12 12 15 15" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    <?= number_format(array_sum($data['daily_stats']), 1) ?> Heures
                                </div>
                                <div class="text-muted">
                                    Total sur 6 mois
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-green text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">
                                    <?= count(array_filter($data['daily_stats'], function($v) { return $v >= 7.5; })) ?> Jours
                                </div>
                                <div class="text-muted">
                                    Plein temps (> 7.5h)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/Views/inc/header.php'; ?>

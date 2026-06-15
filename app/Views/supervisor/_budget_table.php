<div class="table-responsive">
    <table class="table table-vcenter card-table table-striped table-sm">
        <thead>
            <tr>
                <th class="w-1">#</th>
                <th class="w-3">Code</th>
                <th>Libellé</th>
                <th>Unité</th>
                <th class="text-end">Qte</th>
                <th class="text-end">P.U.</th>
                <th class="text-end">Montant Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $grand_total_budget = 0; foreach($data['budgetMainLines'] as $main): ?>
                <tr class="bg-light fw-bold">
                    <td>
                        <div class="dropdown">
                            <a href="#" class=" dropdown-toggle align-text-top" data-bs-toggle="dropdown"></a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <button class="dropdown-item" onclick="openAddDetailModal(<?= $main->id ?>, '<?= htmlspecialchars($main->code) ?>')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                    Ajouter Détail
                                </button>
                                <button class="dropdown-item" onclick='openEditMainLineModal(<?= htmlspecialchars(json_encode($main), ENT_QUOTES, "UTF-8") ?>)'>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                                    Modifier
                                </button>
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item text-danger" onclick="deleteBudgetMainLine(<?= $main->id ?>)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($main->code) ?></td>
                    <td><?= htmlspecialchars($main->label) ?></td>
                    <td colspan="3"></td>
                    <td class="text-end">
                        <?php 
                            $main_total = array_reduce($main->details, function($carry, $item) { return $carry + $item->amount; }, 0);
                            $grand_total_budget += $main_total;
                            echo number_format($main_total, 2) . ' USD';
                        ?>
                    </td>
                </tr>
                <?php foreach($main->details as $detail): ?>
                    <tr>
                        <td>
                            <div class="dropdown">
                                <a href="#" class=" dropdown-toggle align-text-top" data-bs-toggle="dropdown"></a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <button class="dropdown-item" onclick='openEditDetailLineModal(<?= htmlspecialchars(json_encode($detail), ENT_QUOTES, "UTF-8") ?>)'>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                                        Modifier
                                    </button>
                                    <button class="dropdown-item text-danger" onclick="deleteBudgetDetailLine(<?= $detail->id ?>)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="ps-4 text-muted small"><?= htmlspecialchars($detail->code) ?></td>
                        <td class="ps-4"><?= htmlspecialchars($detail->label) ?></td>
                        <td><?= htmlspecialchars($detail->unit ?? '-') ?></td>
                        <td class="text-end"><?= number_format($detail->quantity, 2) ?></td>
                        <td class="text-end"><?= number_format($detail->unit_price, 2) ?></td>
                        <td class="text-end fw-bold"><?= number_format($detail->amount, 2) ?> USD</td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php if(empty($data['budgetMainLines'])): ?>
                <tr><td colspan="7" class="text-center text-muted">Aucune ligne budgétaire.</td></tr>
            <?php else: ?>
                <tr class="fw-bold bg-dark text-white">
                    <td colspan="6" class="text-end">TOTAL BUDGET MISSION</td>
                    <td class="text-end"><?= number_format($grand_total_budget, 2) ?> USD</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

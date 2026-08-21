<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ordre de Mission - <?= htmlspecialchars($data['order']->order_number) ?></title>
    <style>
        @page { size: A4; margin: 0; }
        body { 
            font-family: Verdana, Geneva, Tahoma, sans-serif; 
            line-height: 1.5; 
            color: #000; 
            margin: 0; 
            padding: 15mm 20mm 45mm 20mm; 
            width: 210mm; 
            min-height: 297mm; 
            position: relative; 
            box-sizing: border-box; 
            font-size: 14px;
        }
        .preview-header-dynamic { display: flex; align-items: center; gap: 30px; border-bottom: 2px solid yellow; padding-bottom: 15px; margin-bottom: 30px; }
        .preview-logo { height: 90px; }
        .preview-agency-info { flex: 1; font-size: 11px; line-height: 1.4; color: #000; text-align: center; }
        .doc-title { text-align: center; margin-bottom: 40px; }
        .doc-title h1 { text-transform: uppercase; border: 2px solid #000; display: inline-block; padding: 10px 30px; font-size: 22px; font-weight: bold; margin: 0; }
        .content-section { margin-bottom: 20px; }
        .label { font-weight: bold; width: 240px; display: inline-block; font-size: 16px; text-transform: uppercase; }
        .value { font-size: 16px; }
        .footer-signatures { margin-top: 50px; }
        .date-line { text-align: right; margin-bottom: 20px; font-size: 16px; }
        .signature-block { text-align: left; width: 400px; position: relative; }
        .signature-img { position: absolute; top: -30px; left: 30px; width: 550px; opacity: 0.85; pointer-events: none; z-index: 10; }
        .stamp-zone { margin-top: 15px; min-height: 120px; }
        .foot-img { position: absolute; bottom: 0; left: 0; width: 100%; height: auto; z-index: 1000; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 15mm 20mm 45mm 20mm; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="position: fixed; top: 10px; right: 10px; z-index: 2000;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #206bc4; color: white; border: none; border-radius: 4px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">IMPRIMER</button>
        <button onclick="window.history.back()" style="padding: 10px 20px; cursor: pointer; background: #666; color: white; border: none; border-radius: 4px; margin-left: 5px;">RETOUR</button>
    </div>

    <div class="header">
        <div class="preview-header-dynamic">
            <img src="<?= URLROOT ?>/assets/dwac.png" class="preview-logo" alt="Logo">
            <div class="preview-agency-info">
                <div style="font-weight: bold; font-size: 14px; margin-bottom: 5px;"><?= e($data['order']->agency_name ?? $data['tenant']->name) ?></div>
                <div style="white-space: pre-wrap;"><?= e($data['order']->agency_address ?? $data['tenant']->address) ?></div>
                <div style="margin-top: 5px;">Tél: <?= e($data['order']->agency_phone ?? $data['tenant']->phone) ?></div>
            </div>
        </div>
    </div>

    <div class="doc-title">
        <h1>Ordre de Mission N°<?= e($data['order']->order_number) ?></h1>
    </div>

    <div class="content-section">
        <p><span class="label">NOMS :</span> 
            <span class="value">
            <?php if($data['order']->type == 'collectif'): ?>
                <strong>COLLECTIF (Toute l'équipe de la mission)</strong>
            <?php else: ?>
                <strong><?= e($data['order']->prenom . ' ' . $data['order']->nom) ?></strong>
            <?php endif; ?>
            </span>
        </p>
        <p><span class="label">FONCTION :</span> 
            <span class="value"><?= e($data['order']->signatory_role ?? ($data['employee'] ? ($data['employee']->poste_name ?? 'Agent') : 'Equipe de mission')) ?></span>
        </p>
        <p><span class="label">OBJET :</span> <span class="value"><?= e($data['order']->object) ?></span></p>
        <p><span class="label">LIEUX :</span> <span class="value"><?= e($data['order']->itinerary ?? '-') ?></span></p>
        <p><span class="label">DURÉE :</span> 
            <span class="value">
            <?php 
                $start = new DateTime($data['order']->departure_date);
                $end = new DateTime($data['order']->return_date);
                $days = $start->diff($end)->days + 1;
                $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
                echo $days . " jours (du " . $formatter->format($start) . " au " . $formatter->format($end) . ")";
            ?>
            </span>
        </p>
        <p><span class="label">MOYEN DE DÉPLACEMENT :</span> <span class="value"><?= e($data['order']->means_of_transport ?? '-') ?></span></p>
    </div>

    <div class="content-section" style="text-align: justify; margin-top: 60px; font-size: 16px; line-height: 1.6;">
        <p><?= nl2br(e($data['order']->footer_text ?? 'Nous prions aux Autorités Politico-Administratives, militaires et policières de faciliter libre passage, d’apporter assistance et accorder l’immunité liée aux fonctions du porteur de ce document.')) ?></p>
    </div>

    <div class="footer-signatures">
        <div class="date-line">
            Fait à <?= e($data['order']->sign_city ?? 'Kinshasa') ?>, le <?= $data['order']->validated_at ? (new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE))->format(new DateTime($data['order']->validated_at)) : (new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE))->format(new DateTime()) ?>
        </div>
        <div class="signature-block">
            <p><strong>Pour <?= e($data['order']->agency_name ?? $data['tenant']->name) ?>,</strong></p>
            <div class="stamp-zone">
                <?php if($data['order']->status === 'Validé'): ?>
                        <img src="<?= URLROOT ?>/assets/signature_cachet.png" class="signature-img" alt="Signature">
                <?php else: ?>
                    <span style="color: red; font-weight: bold; border: 4px double red; padding: 10px; display: inline-block; transform: rotate(-5deg); margin-top: 20px;">
                        DOCUMENT NON VALIDE
                    </span>
                <?php endif; ?>
            </div>
            <p style="margin-top: 10px;"><strong><?= e($data['order']->signatory_name ?? 'NGUBI Mac') ?></strong></p>
            <p><strong><?= e($data['order']->signatory_role ?? 'Managing Director') ?></strong></p>
        </div>
    </div>

    <img src="<?= URLROOT ?>/assets/foot.png" class="foot-img" alt="Pied de page">
</body>
</html>

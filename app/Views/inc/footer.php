        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center flex-row-reverse">
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">
                                Copyright &copy; <?= date('Y') ?> <?= SITENAME ?>. Tous droits réservés.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Modal pour visualiser les fichiers -->
<div class="modal modal-blur fade" id="modal-view-file" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="view-file-title">Visualisation du fichier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe id="view-file-frame" src="" width="100%" height="100%" style="border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a id="view-file-download" href="" class="btn btn-primary" target="_blank" download>Télécharger</a>
            </div>
        </div>
    </div>
</div>

<!-- Tabler Core -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>

<script>
function viewFile(url, title = 'Visualisation du fichier') {
    const modalElement = document.getElementById('modal-view-file');
    const frame = document.getElementById('view-file-frame');
    const titleElement = document.getElementById('view-file-title');
    const downloadBtn = document.getElementById('view-file-download');
    
    titleElement.innerText = title;
    frame.src = url;
    downloadBtn.href = url;
    
    let modal = bootstrap.Modal.getInstance(modalElement);
    if (!modal) modal = new bootstrap.Modal(modalElement);
    modal.show();
}
</script>
</body>
</html>

<script>
/*
 * Protection CSRF côté client.
 * Attache automatiquement l'en-tête X-CSRF-Token (lu depuis la balise meta
 * "csrf-token") à toute requête non sûre (POST/PUT/PATCH/DELETE) et same-origin,
 * pour fetch() comme pour XMLHttpRequest. Cela évite de modifier chaque appel
 * AJAX du projet ; le serveur valide le jeton via csrf_check_or_die().
 * Chargé dans le <head> pour patcher fetch/XHR avant tout autre script.
 */
(function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta ? meta.getAttribute('content') : '';
    if (!token) return;

    var UNSAFE = /^(POST|PUT|PATCH|DELETE)$/i;

    function sameOrigin(url) {
        try {
            return new URL(url, window.location.href).origin === window.location.origin;
        } catch (e) {
            return true; // URL relative => même origine
        }
    }

    // --- fetch ---
    if (window.fetch) {
        var origFetch = window.fetch;
        window.fetch = function (input, init) {
            init = init || {};
            var method = init.method
                || (typeof input === 'object' && input ? input.method : null)
                || 'GET';
            var url = (typeof input === 'string') ? input : (input && input.url) || '';
            if (UNSAFE.test(method) && sameOrigin(url)) {
                var headers = new Headers(
                    init.headers
                    || (typeof input === 'object' && input ? input.headers : null)
                    || {}
                );
                if (!headers.has('X-CSRF-Token')) {
                    headers.set('X-CSRF-Token', token);
                }
                init.headers = headers;
            }
            return origFetch.call(this, input, init);
        };
    }

    // --- XMLHttpRequest ---
    var origOpen = XMLHttpRequest.prototype.open;
    var origSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url) {
        this.__csrfUnsafe = UNSAFE.test(method) && sameOrigin(url);
        return origOpen.apply(this, arguments);
    };
    XMLHttpRequest.prototype.send = function () {
        if (this.__csrfUnsafe) {
            try { this.setRequestHeader('X-CSRF-Token', token); } catch (e) {}
        }
        return origSend.apply(this, arguments);
    };

    // --- Injection du champ caché dans les formulaires POST (soumission native) ---
    // Tout <form> en POST reçoit automatiquement un champ csrf_token s'il n'en a
    // pas déjà un, pour que la soumission classique passe la vérification serveur.
    function injectForms() {
        var forms = document.querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) {
            var f = forms[i];
            var method = (f.getAttribute('method') || 'get').toUpperCase();
            if (method !== 'POST') continue;
            if (f.querySelector('input[name="csrf_token"]')) continue;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = token;
            f.appendChild(input);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectForms);
    } else {
        injectForms();
    }
})();
</script>

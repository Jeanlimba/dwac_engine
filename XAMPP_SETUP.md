# Configuration pour XAMPP

Pour faire fonctionner cette application sous XAMPP, suivez ces étapes :

## 1. Déplacer les fichiers
Copiez le dossier `evolution` dans le répertoire `htdocs` de votre installation XAMPP (généralement `C:\xampp\htdocs\evolution`).

## 2. Créer la base de données
1. Ouvrez **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Créez une nouvelle base de données nommée `evolution`.
3. Cliquez sur l'onglet **Importer**.
4. Sélectionnez le fichier `database.sql` situé à la racine du projet et cliquez sur **Importer**.
5. Importez ensuite les autres fichiers `.sql` si nécessaire (dans cet ordre : `departments.sql`, `employees.sql`, etc.).

## 3. Configuration de la base de données
Vérifiez le fichier `config/database.php`. Par défaut, il est configuré pour :
- Host : `localhost`
- Base de données : `evolution`
- Utilisateur : `root`
- Mot de passe : (vide)

C'est la configuration par défaut de XAMPP.

## 4. Accéder à l'application
L'application est accessible via l'URL suivante :
`http://localhost/evolution/`

---

## Facultatif : Configurer un Virtual Host (pour avoir evolution.test)
Si vous voulez utiliser une URL comme `http://evolution.test` au lieu de `http://localhost/evolution/` :

1. Modifiez le fichier `C:\Windows\System32\drivers\etc\hosts` (en tant qu'administrateur) et ajoutez :
   ```
   127.0.0.1 evolution.test
   ```

2. Modifiez le fichier `C:\xampp\apache\conf\extra\httpd-vhosts.conf` et ajoutez :
   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs/evolution"
       ServerName evolution.test
       <Directory "C:/xampp/htdocs/evolution">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. Redémarrez Apache dans le panneau de contrôle XAMPP.
4. Vous pouvez maintenant accéder à l'application via `http://evolution.test`.

@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<p class="muted">LeafCMS est le constructeur de site que j’ai écrit avec Laravel. La licence est MIT. Le <strong>thème</strong> décide de l’apparence. Les <strong>modèles de contenu</strong> décident des champs. Il n’y a pas de boutique d’applications ni de mise à jour en ligne. Ce guide dit la même chose que le README.</p>
<p class="muted">Voir <a href="{{ $urls['helpAdmin'] ?? '#' }}">Admin</a> pour chaque entrée de la barre, <a href="{{ $urls['helpThemes'] ?? '#' }}">Thèmes</a> pour changer d’habillage, <a href="{{ $urls['theme'] ?? '#' }}">Modèles</a> pour les fichiers Blade, et <a href="{{ $urls['tags'] ?? '#' }}">Balises</a> pour les balises de données. Sur un site vide, ne commencez pas par modifier les modèles. Mettez d’abord une page en ligne et publiez un contenu.</p>

<div class="help-jumps">
    @if(!empty($urls['settings']))
        <a href="{{ $urls['settings'] }}">Nom du site</a>
    @endif
    @if(!empty($urls['themes']))
        <a href="{{ $urls['themes'] }}">Changer de thème</a>
    @endif
    @if(!empty($urls['categories']))
        <a href="{{ $urls['categories'] }}">Ajouter une rubrique</a>
    @endif
    @if(!empty($urls['entries']))
        <a href="{{ $urls['entries'] }}">Écrire un contenu</a>
    @endif
    @if(!empty($urls['cache']))
        <a href="{{ $urls['cache'] }}">Vider le cache</a>
    @endif
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">Voir le site</a>
</div>

<h3>Premiers pas</h3>
<ol class="help-steps">
    <li>Dans {!! $hl('settings', 'Réglages') !!}, indiquez le nom du site et une courte présentation.</li>
    <li>Dans {!! $hl('themes', 'Thèmes') !!}, choisissez un habillage et cliquez sur « Utiliser sur ce site ». Les modèles manquants sont ajoutés ; les contenus existants restent.</li>
    <li>Dans {!! $hl('categories', 'Rubriques') !!}, ajoutez des canaux (actus, produits, à propos). Chaque rubrique a besoin d’un modèle pour que la liste connaisse les champs.</li>
    <li>Dans {!! $hl('entries', 'Entrées') !!}, écrivez les articles. Qui a le droit de publier peut publier ; les rédacteurs enregistrent par défaut un brouillon ou une relecture.</li>
    <li>Ouvrez le site. S’il a encore l’ancien aspect après un changement de thème, videz le {!! $hl('cache', 'Cache') !!}.</li>
</ol>
<p class="hint">Avec les données de démo, rubriques et contenus sont déjà là : il suffit de modifier le texte. Sur un site vide, commencez par les rubriques. Une entrée absente de la barre signifie que ce compte n’a pas le droit.</p>

<h3>À quoi sert la barre</h3>
<table class="help-doc">
    <thead>
        <tr><th>Zone</th><th>Ce que vous faites</th></tr>
    </thead>
    <tbody>
        <tr><td>Contenu</td><td>{!! $hl('categories', 'Rubriques') !!}, {!! $hl('entries', 'entrées') !!}, {!! $hl('entryTags', 'étiquettes') !!}, {!! $hl('media', 'médias') !!}, {!! $hl('submissions', 'boîte') !!}, {!! $hl('comments', 'commentaires') !!}. La configuration du contenu contient {!! $hl('flags', 'repères') !!}, {!! $hl('recommends', 'blocs') !!}, {!! $hl('models', 'modèles') !!}, {!! $hl('menus', 'menus') !!}, {!! $hl('forms', 'formulaires') !!} et la {!! $hl('recycle', 'corbeille') !!}.</td></tr>
        <tr><td>Exploitation</td><td>{!! $hl('members', 'Membres') !!}, {!! $hl('collectors', 'collecteurs') !!}, {!! $hl('cache', 'cache') !!} (cache de page et HTML disque). Les outils de gestion contiennent {!! $hl('friendLinks', 'liens') !!}, le {!! $hl('sitemap', 'plan du site') !!}, les {!! $hl('redirects', 'redirections') !!} et les {!! $hl('groups', 'groupes') !!}.</td></tr>
        <tr><td>Système</td><td>{!! $hl('settings', 'Réglages') !!}, {!! $hl('themes', 'thèmes') !!}, langues, journaux, admins, droits, et cette aide. L’aide reste ici pour que les comptes sans droits d’exploitation puissent l’ouvrir.</td></tr>
    </tbody>
</table>

<h3>Rubriques et contenus</h3>
<p class="muted">Types courants : une <strong>liste</strong> de documents, une page <strong>indépendante</strong> (à propos), ou un <strong>lien externe</strong>. Un contenu a une rubrique principale (elle fixe l’URL) et peut aussi figurer dans d’autres rubriques.</p>
<p class="muted">Le modèle fixe les champs. Un article a en général titre, couverture et corps ; un produit ajoute le prix. Les {!! $hl('models', 'Modèles') !!} se copient, s’exportent en JSON et s’importent sans effacer les documents existants.</p>
<p class="muted">Publication programmée : avant l’heure, le site renvoie 404. Un brouillon peut avoir un lien d’aperçu qui ne compte pas les vues et n’est pas mis en cache.</p>

<h3>Menus, blocs, formulaires</h3>
<p class="muted">Si une rubrique est marquée « menu » et que le <code>@@cmsMenu</code> du thème n’a pas encore de menu, ces rubriques deviennent l’en-tête. Pour un ordre précis, construisez un menu d’en-tête dans {!! $hl('menus', 'Menus') !!}.</p>
<p class="muted">Les bannières et les blocs « dernières actus » passent par les {!! $hl('recommends', 'Blocs') !!}. L’identifiant doit correspondre au <code>name</code> du modèle, par exemple <code>home_banner</code>. Les éléments saisis à la main peuvent choisir une image dans la médiathèque et l’afficher ; sortez-la avec <code>cms_media($item-&gt;image)</code>.</p>
<p class="muted">Les liens partenaires de l’accueil sont sous Exploitation → Outils de gestion → {!! $hl('friendLinks', 'Liens') !!}, stockés dans le bloc <code>partners</code> (ou <code>friend_links</code>). Ce n’est pas le modèle « lien » du thème nav, qui est un annuaire d’URL.</p>
<p class="muted">Créez les formulaires de contact dans {!! $hl('forms', 'Formulaires') !!} et sortez-les avec <code>@@cmsForm</code>. Les envois arrivent dans la Boîte. Les commentaires attendent une validation par défaut ; les interrupteurs sont dans {!! $settingsLink('interact', 'Réglages → Règles des commentaires') !!}. La liste d’URL pour les moteurs est sous Exploitation → Outils de gestion → {!! $hl('sitemap', 'Plan du site') !!}.</p>

<h3>Membres</h3>
<p class="muted">La zone <code>/member</code> du site gère l’inscription et la connexion. Si un modèle autorise les contributions et que le groupe a ce droit, les membres peuvent envoyer des brouillons à relire. Les commentaires et les commentaires d’invités se règlent dans les réglages.</p>

<h3>Sauvegardes et cache</h3>
<p class="muted">Sauvegarde et restauration sont sous Système → Maintenance, réservées au super administrateur. Les règles robots.txt supplémentaires sont sous {!! $settingsLink('seo', 'Réglages → Recherche et statistiques') !!} ; la sauvegarde passe aussi par <code>php artisan cms:backup</code>. Cache et HTML statique sont dans {!! $hl('cache', 'Cache') !!}. nginx et le stockage objet sont sous <a href="{{ $urls['env'] ?? '#' }}">Environnement</a>.</p>

<h3>Questions fréquentes</h3>
<div class="help-faq">
    <details>
        <summary>J’ai changé de thème, le site a encore l’ancien aspect</summary>
        <p>Le cache de page est encore là. Dans {!! $hl('cache', 'Cache') !!}, cliquez sur « Vider le cache », ou lancez <code>php artisan cms:html-clear</code>.</p>
    </details>
    <details>
        <summary>J’ai écrit un contenu mais je ne le vois pas</summary>
        <p>Il doit être <strong>publié</strong> (pas brouillon ni en relecture), l’heure doit être passée, et il doit être dans la bonne rubrique. Les rédacteurs ne publient pas par défaut : un super admin ou quelqu’un avec « Publier les contenus » doit le faire.</p>
    </details>
    <details>
        <summary>Images ou fichiers en 404</summary>
        <p>Lancez <code>php artisan storage:link</code> à la racine, et laissez <code>storage</code> et <code>bootstrap/cache</code> accessibles en écriture. Avec un CDN, vérifiez le domaine des fichiers dans Réglages → CDN et API. Avec <code>CMS_MEDIA_DISK=s3</code>, les fichiers ne sont pas sous <code>public/storage</code> : vérifiez le bucket et <code>AWS_URL</code>.</p>
    </details>
    <details>
        <summary>Un bloc ou un formulaire est vide</summary>
        <p>L’identifiant de l’admin doit correspondre au modèle : <code>name</code> du bloc (ex. <code>home_banner</code>), <code>slug</code> du formulaire (ex. <code>contact</code>). Un élément désactivé ou une liste vide n’apparaît pas. Si l’image est dans l’admin mais pas sur le site, le thème doit utiliser <code>cms_media($item-&gt;image)</code>.</p>
    </details>
    <details>
        <summary>Les moteurs doivent ignorer certains chemins</summary>
        <p>Ouvrez d’abord <code>/robots.txt</code> (admin, membres, installation et aperçu sont déjà interdits). Ajoutez des lignes dans {!! $settingsLink('seo', 'Réglages → Recherche et statistiques') !!}, par exemple <code>Disallow: /private</code>. Site fermé, tout le fichier reste <code>Disallow: /</code>. N’écrivez pas de balises HTML. Ce qui est indexé se règle sous Exploitation → Outils de gestion → {!! $hl('sitemap', 'Plan du site') !!}.</p>
    </details>
    <details>
        <summary>Où est le plan du site</summary>
        <p>Les moteurs utilisent <code>/sitemap.xml</code> (anglais : <code>/en/sitemap.xml</code>). Les visiteurs ouvrent <code>/sitemap</code> ; désactiver cette page retire aussi le lien du pied de page. Rubriques, contenus, étiquettes et URL supplémentaires sont sous Exploitation → Outils de gestion → {!! $hl('sitemap', 'Plan du site') !!}. Les contenus réservés aux membres et les étiquettes sans article public sont ignorés. N’utilisez pas sitemap comme identifiant de rubrique.</p>
    </details>
    <details>
        <summary>Relancer l’installateur</summary>
        <p>Supprimez <code>storage/app/install.lock</code> et ouvrez <code>/install</code>. Les données existantes restent. Ne le faites pas sur un site en production.</p>
    </details>
    <details>
        <summary>Le site en ligne affiche un dossier ou une 404</summary>
        <p>La racine web doit être le dossier <code>public</code> du projet, pas la racine du projet. nginx, PHP et MySQL sont sous <a href="{{ $urls['env'] ?? '#' }}">Environnement</a>.</p>
    </details>
</div>

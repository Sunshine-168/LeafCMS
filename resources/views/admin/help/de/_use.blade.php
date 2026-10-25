@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<p class="muted">LeafCMS ist der Website-Baukasten, den ich mit Laravel geschrieben habe. Die Lizenz ist MIT. Das <strong>Theme</strong> bestimmt das Aussehen. <strong>Inhaltsmodelle</strong> bestimmen die Felder. Es gibt keinen App-Store und kein Online-Upgrade. Diese Anleitung sagt dasselbe wie die README.</p>
<p class="muted">Unter <a href="{{ $urls['helpAdmin'] ?? '#' }}">Admin</a> steht jeder Seitenleistenpunkt, unter <a href="{{ $urls['helpThemes'] ?? '#' }}">Themes</a> der Vorlagenwechsel, unter <a href="{{ $urls['theme'] ?? '#' }}">Templates</a> die Blade-Dateien und unter <a href="{{ $urls['tags'] ?? '#' }}">Tags</a> die Daten-Tags. Auf einer leeren Site nicht mit Templates anfangen. Zuerst eine Seite online stellen und einen Eintrag veröffentlichen.</p>

<div class="help-jumps">
    @if(!empty($urls['settings']))
        <a href="{{ $urls['settings'] }}">Site-Name</a>
    @endif
    @if(!empty($urls['themes']))
        <a href="{{ $urls['themes'] }}">Theme wechseln</a>
    @endif
    @if(!empty($urls['categories']))
        <a href="{{ $urls['categories'] }}">Rubrik anlegen</a>
    @endif
    @if(!empty($urls['entries']))
        <a href="{{ $urls['entries'] }}">Eintrag schreiben</a>
    @endif
    @if(!empty($urls['cache']))
        <a href="{{ $urls['cache'] }}">Cache leeren</a>
    @endif
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">Website ansehen</a>
</div>

<h3>Empfohlene erste Schritte</h3>
<ol class="help-steps">
    <li>Unter {!! $hl('settings', 'Einstellungen') !!} Site-Name und eine kurze Vorstellung setzen.</li>
    <li>Unter {!! $hl('themes', 'Themes') !!} eine Vorlage wählen und „Auf dieser Site verwenden“ klicken. Fehlende Modelle werden ergänzt, vorhandene Einträge bleiben.</li>
    <li>Unter {!! $hl('categories', 'Rubriken') !!} Kanäle anlegen (News, Produkte, Über uns). Jede Rubrik braucht ein Modell, damit die Liste die Felder kennt.</li>
    <li>Unter {!! $hl('entries', 'Einträge') !!} Artikel schreiben. Wer veröffentlichen darf, kann sofort live schalten; Redakteure speichern standardmäßig Entwurf oder Prüfung.</li>
    <li>Die Website öffnen. Sieht sie nach einem Theme-Wechsel noch alt aus, {!! $hl('cache', 'Cache') !!} leeren.</li>
</ol>
<p class="hint">Mit Demodaten sind Rubriken und Einträge schon da — nur den Text ändern. Auf einer leeren Site mit Rubriken anfangen. Fehlende Seitenleistenpunkte bedeuten: dieses Konto hat die Berechtigung nicht.</p>

<h3>Wofür die Seitenleiste da ist</h3>
<table class="help-doc">
    <thead>
        <tr><th>Bereich</th><th>Was Sie tun</th></tr>
    </thead>
    <tbody>
        <tr><td>Inhalt</td><td>{!! $hl('categories', 'Rubriken') !!}, {!! $hl('entries', 'Einträge') !!}, {!! $hl('entryTags', 'Schlagwörter') !!}, {!! $hl('media', 'Medien') !!}, {!! $hl('submissions', 'Posteingang') !!}, {!! $hl('comments', 'Kommentare') !!}. Unter Inhaltseinstellungen liegen {!! $hl('flags', 'Kennzeichen') !!}, {!! $hl('recommends', 'Empfehlungen') !!}, {!! $hl('models', 'Modelle') !!}, {!! $hl('menus', 'Menüs') !!}, {!! $hl('forms', 'Formulare') !!} und der {!! $hl('recycle', 'Papierkorb') !!}.</td></tr>
        <tr><td>Betrieb</td><td>{!! $hl('members', 'Mitglieder') !!}, {!! $hl('collectors', 'Sammler') !!}, {!! $hl('cache', 'Cache') !!} (Seitencache und statisches HTML). Unter Betriebswerkzeuge liegen {!! $hl('friendLinks', 'Partnerlinks') !!}, {!! $hl('sitemap', 'Sitemap') !!}, {!! $hl('redirects', 'Weiterleitungen') !!} und {!! $hl('groups', 'Gruppen') !!}.</td></tr>
        <tr><td>System</td><td>{!! $hl('settings', 'Einstellungen') !!}, {!! $hl('themes', 'Themes') !!}, Sprachen, Protokoll, Admins, Rechte und diese Hilfe. Die Hilfe bleibt hier, damit Konten ohne Betriebsrechte sie trotzdem öffnen können.</td></tr>
    </tbody>
</table>

<h3>Rubriken und Einträge</h3>
<p class="muted">Übliche Rubrikarten: eine <strong>Liste</strong> von Dokumenten, eine <strong>Einzelseite</strong> (Über uns) oder ein <strong>externer Link</strong>. Ein Eintrag hat eine Hauptrubrik (die setzt die URL) und kann in weiteren Rubriken erscheinen.</p>
<p class="muted">Das Modell setzt die Felder. Artikel haben meist Titel, Titelbild und Text; Produkte ergänzen Preis und Ähnliches. {!! $hl('models', 'Modelle') !!} lassen sich kopieren, als JSON exportieren und importieren, ohne vorhandene Dokumente zu löschen.</p>
<p class="muted">Zeitgesteuert veröffentlichen: vor dem Zeitpunkt liefert die Website 404. Entwürfe können einen Vorschaulink bekommen, der keine Aufrufe zählt und nicht im Cache landet.</p>

<h3>Menüs, Blöcke, Formulare</h3>
<p class="muted">Ist eine Rubrik als „Menü“ markiert und das Theme-<code>@@cmsMenu</code> hat noch kein Menü, werden diese Rubriken zur Kopfzeile. Für eine feste Reihenfolge ein Kopfmenü unter {!! $hl('menus', 'Menüs') !!} bauen.</p>
<p class="muted">Startseiten-Banner und „letzte Nachrichten“ nutzen {!! $hl('recommends', 'Empfehlungen') !!}. Die Kennung muss zum <code>name</code> im Template passen, zum Beispiel <code>home_banner</code>. Manuell gefüllte Einträge können ein Bild aus der Medienbibliothek wählen und eine Vorschau zeigen; ausgeben mit <code>cms_media($item-&gt;image)</code>.</p>
<p class="muted">Partnerlinks der Startseite liegen unter Betrieb → Betriebswerkzeuge → {!! $hl('friendLinks', 'Partnerlinks') !!}, gespeichert als Block <code>partners</code> (oder <code>friend_links</code>). Das ist nicht das Link-Modell des Nav-Themes, das ein URL-Verzeichnis ist.</p>
<p class="muted">Kontaktformulare unter {!! $hl('forms', 'Formulare') !!} anlegen und mit <code>@@cmsForm</code> ausgeben. Einsendungen landen im Posteingang. Kommentare warten standardmäßig auf Freigabe; Schalter unter {!! $settingsLink('interact', 'Einstellungen → Kommentarregeln') !!}. Die URL-Liste für Suchmaschinen liegt unter Betrieb → Betriebswerkzeuge → {!! $hl('sitemap', 'Sitemap') !!}.</p>

<h3>Mitglieder</h3>
<p class="muted">Der Bereich <code>/member</code> auf der Website ist Registrierung und Anmeldung. Erlaubt ein Modell Mitgliederbeiträge und die Gruppe hat das Recht, können Mitglieder Entwürfe zur Prüfung einreichen. Kommentare und Gästekommentare lassen sich in den Einstellungen einschalten.</p>

<h3>Sicherungen und Cache</h3>
<p class="muted">Sichern und Wiederherstellen liegt unter System → Systemwartung, nur für Super-Admins. Zusätzliche robots.txt-Regeln stehen unter {!! $settingsLink('seo', 'Einstellungen → Suche und Statistik') !!}; Sicherungen gehen auch über <code>php artisan cms:backup</code>. Cache und statisches HTML liegen unter {!! $hl('cache', 'Cache') !!}. nginx und Objektspeicher stehen unter <a href="{{ $urls['env'] ?? '#' }}">Umgebung</a>.</p>

<h3>Häufige Fragen</h3>
<div class="help-faq">
    <details>
        <summary>Theme gewechselt, die Website sieht noch alt aus</summary>
        <p>Der Seitencache ist noch da. Unter {!! $hl('cache', 'Cache') !!} „Cache leeren“ klicken oder <code>php artisan cms:html-clear</code> ausführen.</p>
    </details>
    <details>
        <summary>Eintrag geschrieben, aber nicht sichtbar</summary>
        <p>Er muss <strong>veröffentlicht</strong> sein (nicht Entwurf oder Prüfung), der Zeitpunkt muss erreicht sein, und er muss in der richtigen Rubrik liegen. Redakteure können standardmäßig nicht veröffentlichen — ein Super-Admin oder jemand mit „Einträge veröffentlichen“ muss das tun.</p>
    </details>
    <details>
        <summary>Bilder oder Uploads liefern 404</summary>
        <p>Im Projektroot <code>php artisan storage:link</code> ausführen und <code>storage</code> sowie <code>bootstrap/cache</code> beschreibbar lassen. Bei einem CDN die Asset-Domain unter Einstellungen → CDN und API prüfen. Mit <code>CMS_MEDIA_DISK=s3</code> liegen Dateien nicht unter <code>public/storage</code> — Bucket und <code>AWS_URL</code> prüfen.</p>
    </details>
    <details>
        <summary>Ein Block oder Formular ist leer</summary>
        <p>Die Kennung im Admin muss zum Template passen: Block-<code>name</code> (z. B. <code>home_banner</code>), Formular-<code>slug</code> (z. B. <code>contact</code>). Abgeschaltete oder leere Listen erscheinen nicht. Liegt im Admin ein Bild, aber nicht auf der Website, soll das Theme <code>cms_media($item-&gt;image)</code> nutzen.</p>
    </details>
    <details>
        <summary>Suchmaschinen sollen manche Pfade auslassen</summary>
        <p>Zuerst <code>/robots.txt</code> öffnen (Admin, Mitglieder, Installation und Vorschau sind schon gesperrt). Zeilen unter {!! $settingsLink('seo', 'Einstellungen → Suche und Statistik') !!} als „robots.txt-Zusatzregeln“ eintragen, z. B. <code>Disallow: /private</code>. Bei geschlossener Site bleibt die ganze Datei <code>Disallow: /</code>. Keine HTML-Tags schreiben. Was aufgenommen wird, steht unter Betrieb → Betriebswerkzeuge → {!! $hl('sitemap', 'Sitemap') !!}.</p>
    </details>
    <details>
        <summary>Wo ist die Sitemap</summary>
        <p>Suchmaschinen nutzen <code>/sitemap.xml</code> (Englisch: <code>/en/sitemap.xml</code>). Besucher können <code>/sitemap</code> öffnen; wird die Seite abgeschaltet, verschwindet auch der Footer-Link. Rubriken, Einträge, Schlagwörter und Extra-URLs stehen unter Betrieb → Betriebswerkzeuge → {!! $hl('sitemap', 'Sitemap') !!}. Nur für Mitglieder sichtbare Einträge und ungenutzte Schlagwörter fehlen. sitemap nicht als Rubrik-Kennung verwenden.</p>
    </details>
    <details>
        <summary>Den Installer noch einmal</summary>
        <p><code>storage/app/install.lock</code> löschen und <code>/install</code> öffnen. Vorhandene Daten bleiben. Auf einer Live-Site nicht tun.</p>
    </details>
    <details>
        <summary>Die Live-Site zeigt ein Verzeichnis oder 404</summary>
        <p>Das Webroot muss der Ordner <code>public</code> des Projekts sein, nicht das Projektroot. nginx, PHP und MySQL stehen unter <a href="{{ $urls['env'] ?? '#' }}">Umgebung</a>.</p>
    </details>
</div>

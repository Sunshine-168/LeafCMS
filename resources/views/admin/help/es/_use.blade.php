@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<p class="muted">LeafCMS es el constructor de sitios que escribí con Laravel. La licencia es MIT. El <strong>tema</strong> decide el aspecto. Los <strong>modelos de contenido</strong> deciden los campos. No hay tienda de aplicaciones ni actualización en línea. Esta guía dice lo mismo que el README.</p>
<p class="muted">Vea <a href="{{ $urls['helpAdmin'] ?? '#' }}">Admin</a> para cada elemento de la barra, <a href="{{ $urls['helpThemes'] ?? '#' }}">Temas</a> para cambiar la piel, <a href="{{ $urls['theme'] ?? '#' }}">Plantillas</a> para los archivos Blade y <a href="{{ $urls['tags'] ?? '#' }}">Etiquetas</a> para las etiquetas de datos. En un sitio vacío no empiece editando plantillas. Publique primero una página y un contenido.</p>

<div class="help-jumps">
    @if(!empty($urls['settings']))
        <a href="{{ $urls['settings'] }}">Nombre del sitio</a>
    @endif
    @if(!empty($urls['themes']))
        <a href="{{ $urls['themes'] }}">Cambiar tema</a>
    @endif
    @if(!empty($urls['categories']))
        <a href="{{ $urls['categories'] }}">Añadir una sección</a>
    @endif
    @if(!empty($urls['entries']))
        <a href="{{ $urls['entries'] }}">Escribir un contenido</a>
    @endif
    @if(!empty($urls['cache']))
        <a href="{{ $urls['cache'] }}">Vaciar caché</a>
    @endif
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">Ver el sitio</a>
</div>

<h3>Primeros pasos</h3>
<ol class="help-steps">
    <li>En {!! $hl('settings', 'Ajustes') !!}, ponga el nombre del sitio y una presentación breve.</li>
    <li>En {!! $hl('themes', 'Temas') !!}, elija una piel y pulse «Usar en este sitio». Se añaden los modelos que falten; los contenidos existentes se conservan.</li>
    <li>En {!! $hl('categories', 'Secciones') !!}, añada canales (noticias, productos, acerca de). Cada sección necesita un modelo para que la lista sepa qué campos usar.</li>
    <li>En {!! $hl('entries', 'Entradas') !!}, escriba artículos. Quien pueda publicar, publica; los editores guardan borrador o pendiente por defecto.</li>
    <li>Abra el sitio. Si sigue viéndose viejo tras cambiar el tema, vacíe la {!! $hl('cache', 'Caché') !!}.</li>
</ol>
<p class="hint">Con datos de demostración, secciones y contenidos ya están: solo cambie el texto. En un sitio vacío, empiece por las secciones. Si falta un elemento de la barra, esta cuenta no tiene permiso.</p>

<h3>Para qué sirve la barra</h3>
<table class="help-doc">
    <thead>
        <tr><th>Zona</th><th>Qué hace</th></tr>
    </thead>
    <tbody>
        <tr><td>Contenido</td><td>{!! $hl('categories', 'Secciones') !!}, {!! $hl('entries', 'entradas') !!}, {!! $hl('entryTags', 'etiquetas') !!}, {!! $hl('media', 'medios') !!}, {!! $hl('submissions', 'bandeja') !!}, {!! $hl('comments', 'comentarios') !!}. La configuración de contenido incluye {!! $hl('flags', 'marcas') !!}, {!! $hl('recommends', 'bloques') !!}, {!! $hl('models', 'modelos') !!}, {!! $hl('menus', 'menús') !!}, {!! $hl('forms', 'formularios') !!} y la {!! $hl('recycle', 'papelera') !!}.</td></tr>
        <tr><td>Operación</td><td>{!! $hl('members', 'Miembros') !!}, {!! $hl('collectors', 'recolectores') !!}, {!! $hl('cache', 'caché') !!} (caché de página y HTML en disco). Las herramientas de operación incluyen {!! $hl('friendLinks', 'enlaces') !!}, el {!! $hl('sitemap', 'mapa del sitio') !!}, las {!! $hl('redirects', 'redirecciones') !!} y los {!! $hl('groups', 'grupos') !!}.</td></tr>
        <tr><td>Sistema</td><td>{!! $hl('settings', 'Ajustes') !!}, {!! $hl('themes', 'temas') !!}, idiomas, registros, admins, permisos y esta ayuda. La ayuda se queda aquí para que las cuentas sin permisos de operación puedan abrirla.</td></tr>
    </tbody>
</table>

<h3>Secciones y contenidos</h3>
<p class="muted">Tipos habituales: una <strong>lista</strong> de documentos, una página <strong>independiente</strong> (acerca de) o un <strong>enlace externo</strong>. Un contenido tiene una sección principal (define la URL) y puede aparecer en otras.</p>
<p class="muted">El modelo fija los campos. Un artículo suele tener título, portada y cuerpo; un producto añade el precio. Los {!! $hl('models', 'Modelos') !!} se copian, se exportan a JSON y se importan sin borrar los documentos existentes.</p>
<p class="muted">Publicación programada: antes de la hora, el sitio responde 404. Un borrador puede tener un enlace de vista previa que no cuenta visitas ni entra en la caché.</p>

<h3>Menús, bloques, formularios</h3>
<p class="muted">Si una sección está marcada como «menú» y el <code>@@cmsMenu</code> del tema aún no tiene menú, esas secciones pasan a la cabecera. Para un orden fijo, cree un menú de cabecera en {!! $hl('menus', 'Menús') !!}.</p>
<p class="muted">Los banners y los bloques de «últimas noticias» usan {!! $hl('recommends', 'Bloques') !!}. El identificador debe coincidir con el <code>name</code> de la plantilla, por ejemplo <code>home_banner</code>. Los elementos manuales pueden elegir una imagen de la mediateca y previsualizarla; muéstrela con <code>cms_media($item-&gt;image)</code>.</p>
<p class="muted">Los enlaces de la portada están en Operación → Herramientas → {!! $hl('friendLinks', 'Enlaces') !!}, guardados en el bloque <code>partners</code> (o <code>friend_links</code>). No es el modelo «enlace» del tema de navegación, que es un directorio de URL.</p>
<p class="muted">Cree los formularios de contacto en {!! $hl('forms', 'Formularios') !!} y muéstrelos con <code>@@cmsForm</code>. Los envíos llegan a la bandeja. Los comentarios esperan aprobación por defecto; los interruptores están en {!! $settingsLink('interact', 'Ajustes → Reglas de comentarios') !!}. La lista de URL para buscadores está en Operación → Herramientas → {!! $hl('sitemap', 'Mapa del sitio') !!}.</p>

<h3>Miembros</h3>
<p class="muted">La zona <code>/member</code> del sitio es el registro y el acceso. Si un modelo permite aportaciones y el grupo tiene ese permiso, los miembros pueden enviar borradores a revisión. Los comentarios y los de invitados se activan en los ajustes.</p>

<h3>Copias y caché</h3>
<p class="muted">Copiar y restaurar está en Sistema → Mantenimiento, solo para el superadministrador. Las reglas extra de robots.txt están en {!! $settingsLink('seo', 'Ajustes → Búsqueda y estadísticas') !!}; la copia también se hace con <code>php artisan cms:backup</code>. La caché y el HTML estático están en {!! $hl('cache', 'Caché') !!}. nginx y el almacenamiento de objetos están en <a href="{{ $urls['env'] ?? '#' }}">Entorno</a>.</p>

<h3>Preguntas frecuentes</h3>
<div class="help-faq">
    <details>
        <summary>Cambié el tema y el sitio sigue viéndose viejo</summary>
        <p>La caché de página sigue ahí. En {!! $hl('cache', 'Caché') !!} pulse «Vaciar caché», o ejecute <code>php artisan cms:html-clear</code>.</p>
    </details>
    <details>
        <summary>Escribí un contenido y no lo veo</summary>
        <p>Tiene que estar <strong>publicado</strong> (no borrador ni pendiente), la hora ya debe haber llegado y debe estar en la sección correcta. Los editores no publican por defecto: debe hacerlo un superadmin o alguien con «Publicar contenidos».</p>
    </details>
    <details>
        <summary>Imágenes o archivos en 404</summary>
        <p>Ejecute <code>php artisan storage:link</code> en la raíz y deje <code>storage</code> y <code>bootstrap/cache</code> escribibles. Con CDN, revise el dominio en Ajustes → CDN y API. Con <code>CMS_MEDIA_DISK=s3</code> los archivos no están en <code>public/storage</code>: revise el bucket y <code>AWS_URL</code>.</p>
    </details>
    <details>
        <summary>Un bloque o un formulario sale vacío</summary>
        <p>El identificador del admin debe coincidir con la plantilla: <code>name</code> del bloque (p. ej. <code>home_banner</code>), <code>slug</code> del formulario (p. ej. <code>contact</code>). Lo desactivado o vacío no se muestra. Si la imagen está en el admin y no en el sitio, el tema debe usar <code>cms_media($item-&gt;image)</code>.</p>
    </details>
    <details>
        <summary>Los buscadores deben saltarse algunas rutas</summary>
        <p>Abra primero <code>/robots.txt</code> (admin, miembros, instalación y vista previa ya están prohibidos). Añada líneas en {!! $settingsLink('seo', 'Ajustes → Búsqueda y estadísticas') !!}, por ejemplo <code>Disallow: /private</code>. Con el sitio cerrado, todo el archivo sigue siendo <code>Disallow: /</code>. No escriba etiquetas HTML. Lo que se indexa está en Operación → Herramientas → {!! $hl('sitemap', 'Mapa del sitio') !!}.</p>
    </details>
    <details>
        <summary>Dónde está el mapa del sitio</summary>
        <p>Los buscadores usan <code>/sitemap.xml</code> (inglés: <code>/en/sitemap.xml</code>). Los visitantes abren <code>/sitemap</code>; al apagar esa página desaparece también el enlace del pie. Secciones, contenidos, etiquetas y URL extra están en Operación → Herramientas → {!! $hl('sitemap', 'Mapa del sitio') !!}. Los contenidos solo para miembros y las etiquetas sin artículos públicos no entran. No use sitemap como identificador de sección.</p>
    </details>
    <details>
        <summary>Quiero el instalador otra vez</summary>
        <p>Borre <code>storage/app/install.lock</code> y abra <code>/install</code>. Los datos existentes no se borran. No lo haga en un sitio en producción.</p>
    </details>
    <details>
        <summary>El sitio en vivo muestra un directorio o un 404</summary>
        <p>La raíz web debe ser la carpeta <code>public</code> del proyecto, no la raíz del proyecto. nginx, PHP y MySQL están en <a href="{{ $urls['env'] ?? '#' }}">Entorno</a>.</p>
    </details>
</div>

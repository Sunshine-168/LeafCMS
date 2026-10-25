@php
    $urls = $urls ?? [];
    $hl = $hl ?? fn (string $key, string $label) => e($label);
    $settingsLink = static function (string $tab, string $label) use ($urls): string {
        return !empty($urls['settings'])
            ? '<a href="'.e(route('admin.settings.edit', ['tab' => $tab])).'">'.e($label).'</a>'
            : e($label);
    };
@endphp
<p class="muted">O LeafCMS é o construtor de sites que escrevi com Laravel. A licença é MIT. O <strong>tema</strong> decide o aspeto. Os <strong>modelos de conteúdo</strong> decidem os campos. Não há loja de aplicações nem atualização online. Este guia diz o mesmo que o README.</p>
<p class="muted">Veja <a href="{{ $urls['helpAdmin'] ?? '#' }}">Admin</a> para cada item da barra, <a href="{{ $urls['helpThemes'] ?? '#' }}">Temas</a> para mudar a aparência, <a href="{{ $urls['theme'] ?? '#' }}">Modelos</a> para os ficheiros Blade e <a href="{{ $urls['tags'] ?? '#' }}">Etiquetas</a> para as etiquetas de dados. Num site vazio, não comece a editar modelos. Ponha primeiro uma página online e publique um conteúdo.</p>

<div class="help-jumps">
    @if(!empty($urls['settings']))
        <a href="{{ $urls['settings'] }}">Nome do site</a>
    @endif
    @if(!empty($urls['themes']))
        <a href="{{ $urls['themes'] }}">Mudar o tema</a>
    @endif
    @if(!empty($urls['categories']))
        <a href="{{ $urls['categories'] }}">Adicionar uma secção</a>
    @endif
    @if(!empty($urls['entries']))
        <a href="{{ $urls['entries'] }}">Escrever um conteúdo</a>
    @endif
    @if(!empty($urls['cache']))
        <a href="{{ $urls['cache'] }}">Limpar a cache</a>
    @endif
    <a href="{{ $urls['front'] ?? url('/') }}" target="_blank" rel="noopener">Ver o site</a>
</div>

<h3>Primeiros passos</h3>
<ol class="help-steps">
    <li>Em {!! $hl('settings', 'Definições') !!}, indique o nome do site e uma apresentação curta.</li>
    <li>Em {!! $hl('themes', 'Temas') !!}, escolha uma aparência e clique em «Usar neste site». Os modelos em falta são acrescentados; os conteúdos existentes ficam.</li>
    <li>Em {!! $hl('categories', 'Secções') !!}, acrescente canais (notícias, produtos, sobre). Cada secção precisa de um modelo para a lista saber os campos.</li>
    <li>Em {!! $hl('entries', 'Entradas') !!}, escreva artigos. Quem pode publicar, publica; os editores guardam rascunho ou pendente por defeito.</li>
    <li>Abra o site. Se continuar com o aspeto antigo depois de mudar o tema, limpe a {!! $hl('cache', 'Cache') !!}.</li>
</ol>
<p class="hint">Com dados de demonstração, secções e conteúdos já existem: basta editar o texto. Num site vazio, comece pelas secções. Um item em falta na barra significa que esta conta não tem permissão.</p>

<h3>Para que serve a barra</h3>
<table class="help-doc">
    <thead>
        <tr><th>Zona</th><th>O que faz</th></tr>
    </thead>
    <tbody>
        <tr><td>Conteúdo</td><td>{!! $hl('categories', 'Secções') !!}, {!! $hl('entries', 'entradas') !!}, {!! $hl('entryTags', 'etiquetas') !!}, {!! $hl('media', 'média') !!}, {!! $hl('submissions', 'caixa') !!}, {!! $hl('comments', 'comentários') !!}. A configuração de conteúdo inclui {!! $hl('flags', 'marcas') !!}, {!! $hl('recommends', 'blocos') !!}, {!! $hl('models', 'modelos') !!}, {!! $hl('menus', 'menus') !!}, {!! $hl('forms', 'formulários') !!} e a {!! $hl('recycle', 'reciclagem') !!}.</td></tr>
        <tr><td>Operação</td><td>{!! $hl('members', 'Membros') !!}, {!! $hl('collectors', 'coletores') !!}, {!! $hl('cache', 'cache') !!} (cache de página e HTML em disco). As ferramentas de operação incluem {!! $hl('friendLinks', 'ligações') !!}, o {!! $hl('sitemap', 'mapa do site') !!}, os {!! $hl('redirects', 'redirecionamentos') !!} e os {!! $hl('groups', 'grupos') !!}.</td></tr>
        <tr><td>Sistema</td><td>{!! $hl('settings', 'Definições') !!}, {!! $hl('themes', 'temas') !!}, idiomas, registos, admins, permissões e esta ajuda. A ajuda fica aqui para as contas sem permissões de operação a poderem abrir.</td></tr>
    </tbody>
</table>

<h3>Secções e conteúdos</h3>
<p class="muted">Tipos habituais: uma <strong>lista</strong> de documentos, uma página <strong>independente</strong> (sobre) ou uma <strong>ligação externa</strong>. Um conteúdo tem uma secção principal (define o URL) e pode aparecer noutras.</p>
<p class="muted">O modelo fixa os campos. Um artigo costuma ter título, capa e corpo; um produto acrescenta o preço. Os {!! $hl('models', 'Modelos') !!} copiam-se, exportam-se em JSON e importam-se sem apagar os documentos existentes.</p>
<p class="muted">Publicação agendada: antes da hora, o site responde 404. Um rascunho pode ter uma ligação de pré-visualização que não conta visitas nem entra na cache.</p>

<h3>Menus, blocos, formulários</h3>
<p class="muted">Se uma secção estiver marcada como «menu» e o <code>@@cmsMenu</code> do tema ainda não tiver menu, essas secções passam ao cabeçalho. Para uma ordem fixa, crie um menu de cabeçalho em {!! $hl('menus', 'Menus') !!}.</p>
<p class="muted">Banners e blocos de «últimas notícias» usam {!! $hl('recommends', 'Blocos') !!}. O identificador tem de coincidir com o <code>name</code> do modelo, por exemplo <code>home_banner</code>. Os itens manuais podem escolher uma imagem da mediateca e pré-visualizá-la; mostre-a com <code>cms_media($item-&gt;image)</code>.</p>
<p class="muted">As ligações da página inicial estão em Operação → Ferramentas → {!! $hl('friendLinks', 'Ligações') !!}, guardadas no bloco <code>partners</code> (ou <code>friend_links</code>). Não é o modelo «ligação» do tema de navegação, que é um diretório de URL.</p>
<p class="muted">Crie formulários de contacto em {!! $hl('forms', 'Formulários') !!} e mostre-os com <code>@@cmsForm</code>. Os envios vão para a caixa. Os comentários esperam aprovação por defeito; os interruptores estão em {!! $settingsLink('interact', 'Definições → Regras de comentários') !!}. A lista de URL para motores está em Operação → Ferramentas → {!! $hl('sitemap', 'Mapa do site') !!}.</p>

<h3>Membros</h3>
<p class="muted">A zona <code>/member</code> do site trata do registo e do acesso. Se um modelo permitir contributos e o grupo tiver essa permissão, os membros podem enviar rascunhos para revisão. Comentários e comentários de visitantes ligam-se nas definições.</p>

<h3>Cópias e cache</h3>
<p class="muted">Copiar e restaurar está em Sistema → Manutenção, só para o superadministrador. As regras extra de robots.txt estão em {!! $settingsLink('seo', 'Definições → Pesquisa e estatísticas') !!}; a cópia também se faz com <code>php artisan cms:backup</code>. A cache e o HTML estático estão em {!! $hl('cache', 'Cache') !!}. nginx e o armazenamento de objetos estão em <a href="{{ $urls['env'] ?? '#' }}">Ambiente</a>.</p>

<h3>Perguntas frequentes</h3>
<div class="help-faq">
    <details>
        <summary>Mudei o tema e o site continua com o aspeto antigo</summary>
        <p>A cache de página ainda está lá. Em {!! $hl('cache', 'Cache') !!} clique em «Limpar a cache», ou execute <code>php artisan cms:html-clear</code>.</p>
    </details>
    <details>
        <summary>Escrevi um conteúdo e não o vejo</summary>
        <p>Tem de estar <strong>publicado</strong> (não rascunho nem pendente), a hora já deve ter chegado e tem de estar na secção certa. Os editores não publicam por defeito: um superadmin ou alguém com «Publicar conteúdos» tem de o fazer.</p>
    </details>
    <details>
        <summary>Imagens ou ficheiros em 404</summary>
        <p>Execute <code>php artisan storage:link</code> na raiz e deixe <code>storage</code> e <code>bootstrap/cache</code> com escrita. Com CDN, verifique o domínio em Definições → CDN e API. Com <code>CMS_MEDIA_DISK=s3</code> os ficheiros não estão em <code>public/storage</code>: verifique o bucket e <code>AWS_URL</code>.</p>
    </details>
    <details>
        <summary>Um bloco ou formulário aparece vazio</summary>
        <p>O identificador no admin tem de coincidir com o modelo: <code>name</code> do bloco (ex. <code>home_banner</code>), <code>slug</code> do formulário (ex. <code>contact</code>). O que está desligado ou vazio não aparece. Se a imagem está no admin e não no site, o tema deve usar <code>cms_media($item-&gt;image)</code>.</p>
    </details>
    <details>
        <summary>Os motores devem ignorar alguns caminhos</summary>
        <p>Abra primeiro <code>/robots.txt</code> (admin, membros, instalação e pré-visualização já estão bloqueados). Acrescente linhas em {!! $settingsLink('seo', 'Definições → Pesquisa e estatísticas') !!}, por exemplo <code>Disallow: /private</code>. Com o site fechado, o ficheiro inteiro continua <code>Disallow: /</code>. Não escreva etiquetas HTML. O que é indexado está em Operação → Ferramentas → {!! $hl('sitemap', 'Mapa do site') !!}.</p>
    </details>
    <details>
        <summary>Onde está o mapa do site</summary>
        <p>Os motores usam <code>/sitemap.xml</code> (inglês: <code>/en/sitemap.xml</code>). Os visitantes abrem <code>/sitemap</code>; ao desligar essa página some também a ligação do rodapé. Secções, conteúdos, etiquetas e URL extra estão em Operação → Ferramentas → {!! $hl('sitemap', 'Mapa do site') !!}. Conteúdos só para membros e etiquetas sem artigos públicos ficam de fora. Não use sitemap como identificador de secção.</p>
    </details>
    <details>
        <summary>Quero o instalador outra vez</summary>
        <p>Apague <code>storage/app/install.lock</code> e abra <code>/install</code>. Os dados existentes mantêm-se. Não faça isto num site em produção.</p>
    </details>
    <details>
        <summary>O site em produção mostra uma pasta ou um 404</summary>
        <p>A raiz web tem de ser a pasta <code>public</code> do projeto, não a raiz do projeto. nginx, PHP e MySQL estão em <a href="{{ $urls['env'] ?? '#' }}">Ambiente</a>.</p>
    </details>
</div>

@props(['inputId' => 'wall-search-q'])

<label class="sr-only" for="{{ $inputId }}">Buscar publicaciones y etiquetas</label>
<div class="navbar-search w-full">
    <span class="navbar-search__icon" aria-hidden="true">
        <svg class="h-[18px] w-[18px]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
    </span>
    <input
        id="{{ $inputId }}"
        type="search"
        data-wall-search
        enterkeyhint="search"
        autocomplete="off"
        placeholder="Buscar publicaciones o etiquetas…"
        @class([
            'navbar-search__input w-full min-w-0',
            'navbar-search__input--with-kbd' => $inputId === 'wall-search-q',
        ])
    />
    @if ($inputId === 'wall-search-q')
        <kbd class="navbar-search__kbd" aria-hidden="true">/</kbd>
    @endif
</div>

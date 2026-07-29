@php
    $schemaBlocks = $schemaBlocks ?? [];
@endphp

@if (($isPublicPage ?? false) && filled($schemaBlocks))
    @foreach ($schemaBlocks as $schemaBlock)
        <script type="application/ld+json">{!! json_encode($schemaBlock, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
@endif

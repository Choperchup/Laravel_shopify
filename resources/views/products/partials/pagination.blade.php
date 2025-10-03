@if(isset($pageInfo) && (($pageInfo['hasNextPage'] ?? false) || ($pageInfo['hasPreviousPage'] ?? false)))
    <div class="pagination-container">
        @if($pageInfo['hasPreviousPage'])
            <button class="Polaris-Button Polaris-Button--sizeSlim" data-cursor="{{ $pageInfo['startCursor'] }}"
                data-direction="prev" onclick="loadPageWithData(this)">
                ← Previous
            </button>
        @endif
        @if($pageInfo['hasNextPage'])
            <button class="Polaris-Button Polaris-Button--sizeSlim" data-cursor="{{ $pageInfo['endCursor'] }}"
                data-direction="next" onclick="loadPageWithData(this)">
                Next →
            </button>
        @endif
    </div>
@endif
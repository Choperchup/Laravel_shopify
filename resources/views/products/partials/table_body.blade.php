<tbody id="productTableBody">
    @foreach ($products ?? [] as $product)
        @php
            $node = $product['node'];
            $image = $node['media']['edges'][0]['node']['image']['url'] ?? 'https://via.placeholder.com/40';
            $collections = [];
            if (!empty($node['collections']['edges']) && is_array($node['collections']['edges'])) {
                foreach ($node['collections']['edges'] as $cEdge) {
                    $cNode = $cEdge['node'] ?? null;
                    if ($cNode) {
                        $collections[] = [
                            'title' => $cNode['title'] ?? '-',
                            'handle' => $cNode['handle'] ?? null,
                            'id' => $cNode['id'] ?? null,
                        ];
                    }
                }
            }
        @endphp
        <tr>
            <td><input type="checkbox" class="row-check" data-product-id="{{ $node['id'] }}"></td>
            <td>
                <img src="{{ $image }}" alt="" width="40">
                {{ $node['title'] }}
            </td>
            <td>
                <span class="status {{ strtolower($node['status']) }}">
                    {{ $node['status'] }}
                </span>
            </td>
            <td>
                {{ $node['totalInventory'] ?? 0 }} in stock
                for {{ $node['variantsCount']['count'] ?? 0 }} variants
            </td>
            <td>{{ $node['vendor'] ?? '-' }}</td>
            <td>{{ $node['productType'] ?? '-' }}</td>
            <td>{{ !empty($node['tags']) ? implode(', ', $node['tags']) : '-' }}</td>
            <td>
                @if (!empty($collections))
                    @foreach ($collections as $c)
                        <span data-collection-id="{{ $c['id'] }}">{{ $c['title'] }}</span>
                        @if (!$loop->last), @endif
                    @endforeach
                @else
                    -
                @endif
            </td>
        </tr>

    @endforeach
</tbody>
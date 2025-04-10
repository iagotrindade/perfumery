<table class="text-sm text-gray-700 w-full border border-gray-300 rounded">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-2 py-1 text-left">Produto</th>
            <th class="px-2 py-1 text-right">Qtd</th>
            <th class="px-2 py-1 text-right">Valor</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($record->products as $product)
            <tr class="border-t">
                <td class="px-2 py-1">{{ $product->name }}</td>
                <td class="px-2 py-1 text-right">{{ $product->pivot->quantity }}</td>
                <td class="px-2 py-1 text-right">R$ {{ number_format($product->sale_value, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
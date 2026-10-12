@props(['columnas' => [], 'paginacion' => null])

<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead>
            <tr>
                @foreach ($columnas as $columna)
                    <th class="px-4 py-2 text-left font-semibold text-gray-700">
                        {{ $columna }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            {{ $slot }}
        </tbody>
    </table>
</div>

@if ($paginacion)
    <div class="mt-4">
        {{ $paginacion->links() }}
    </div>
@endif